# Runbook 05 - Workers de cola y dispatch programado de campañas

**Severidad:** Media (el envío de emails se detiene, los logs se acumulan y las campañas quedan sin salida) | **ETA:** 10-20 min | **Auditoría:** H-4 / TASK-20

## Cuándo usar

- Los emails de confirmación no se están enviando.
- Las campañas quedan en `scheduled` y nunca pasan a `sending` / `sent`.
- `jobs` crece sin control, o `failed_jobs` empieza a aumentar.
- Necesitas un procedimiento repetible para dev/prod con workers de cola.

## Política de cola

- El almacenamiento de cola usa base de datos.
- Nombres de cola:
  - `emails` para double opt-in y envíos de campañas.
  - `logs` para request logging.
  - `audit` para escrituras asíncronas de auditoría.
- Política de reintentos:
  - `QUEUE_MAX_ATTEMPTS=3`
  - `QUEUE_RETRY_AFTER=90`
- Los jobs relanzan la excepción en fallo para que el queue manager pueda reintentarlos hasta agotar el límite.
- Cuando un job agota el límite de intentos, termina en `failed_jobs` para revisión operativa.

## Pre-flight checks

```bash
php spark migrate

mysql -e "SELECT COUNT(*) AS pending_jobs FROM jobs;" "$DB_NAME"
mysql -e "SELECT COUNT(*) AS failed_jobs FROM failed_jobs;" "$DB_NAME"
```

Si `jobs` está vacío, primero dispara un flujo que encole trabajo:

- Suscribir un usuario para encolar el email de double opt-in.
- Hacer dispatch de una campaña programada para crear deliveries.
- Golpear un endpoint con request logging activo.

## Flujo de desarrollo

Usa un terminal para la app y otro para el worker.

```bash
php spark serve --port 8090
php spark queue:work --queue=emails --once
```

Recomendado mientras depuras:

```bash
php spark queue:work --queue=logs --once
php spark queue:work --queue=audit --once
```

Para procesamiento continuo durante pruebas manuales:

```bash
php spark queue:work --queue=emails
php spark queue:work --queue=logs
php spark queue:work --queue=audit
```

## Flujo de producción

Ejecuta un worker por cola bajo un supervisor de procesos como systemd, supervisor o PM2.

### Ejemplo de worker systemd

```ini
[Unit]
Description=ci4 domain queue worker (emails)
After=network.target mysql.service

[Service]
Type=simple
WorkingDirectory=/var/www/ci4-multi-subscription-domain
ExecStart=/usr/bin/php spark queue:work --queue=emails --sleep=3
Restart=always
RestartSec=5
User=www-data

[Install]
WantedBy=multi-user.target
```

Crea unidades equivalentes para `logs` y `audit`.

### Ejemplo de cron para dispatcher

Ejecuta el dispatcher cada minuto para que las campañas programadas se tomen automáticamente:

```cron
* * * * * cd /var/www/ci4-multi-subscription-domain && /usr/bin/php spark campaign:dispatch >> writable/logs/campaign-dispatch.log 2>&1
```

## Verificación

1. Dispara un evento que encole trabajo.
2. Confirma la fila en `jobs`.
3. Ejecuta `php spark queue:work --queue=emails --once`.
4. Confirma que la fila desaparece de `jobs`.
5. Confirma que el delivery quedó en `sent`, o en `failed` con `last_error` útil.
6. Si hay fallos acumulados, revisa `failed_jobs` y los logs antes de reencolar.

## Smoke E2E con run id aislado

Para corridas con navegador y correo real, usa un identificador único y ponlo en todos los datos creados desde la UI:

```bash
RUN_ID="e2e-$(date +%Y%m%d-%H%M%S)"
```

También puedes preparar una corrida aislada desde el domain sin guardar credenciales externas:

```bash
php spark newsletter:e2e:prepare --run-id="$RUN_ID" --project-key=test-project-key --email=tu-bandeja-controlada@example.test
```

El comando limpia primero datos propios de ese `RUN_ID`, asegura el proyecto, crea una campaña `draft` con nombre/asunto trazables y, si pasas `--email`, crea un subscriber confirmado para probar campaign deliveries. Para limpiar al final sin crear nuevos datos:

```bash
php spark newsletter:e2e:prepare --run-id="$RUN_ID" --project-key=test-project-key --cleanup-only
```

Convenciones recomendadas:

- Campaign name: `Campaign ${RUN_ID}`
- Campaign subject: `E2E Campaign ${RUN_ID}`
- Subscriber email: usar una dirección controlada de la bandeja externa, y registrar el `RUN_ID` en `first_name` o en el nombre de campaña.
- No reutilizar campañas antiguas para verificar métricas.

Antes de ejecutar el worker, confirma que el flujo normal sí dejó jobs en la cola `emails`:

```sql
SELECT id, queue, attempts, reserved_at, available_at, created_at
FROM jobs
WHERE queue = 'emails'
ORDER BY id DESC
LIMIT 10;
```

Procesa la cola sin comandos temporales:

```bash
php spark queue:work --queue=emails --once
```

Para campañas, verifica únicamente las deliveries de la campaña del run:

```sql
SELECT d.id, d.email, d.status, d.attempts, d.last_error, d.sent_at
FROM deliveries d
JOIN campaigns c ON c.id = d.campaign_id
WHERE c.name LIKE CONCAT('%', :run_id, '%')
ORDER BY d.id;
```

Limpieza segura después de una corrida: elimina o archiva solo datos con el `RUN_ID` explícito. No borres por estado (`pending`, `failed`, `sent`) ni por fechas amplias, porque puedes tocar pruebas manuales no relacionadas.

## Troubleshooting

- Los jobs no arrancan:
  - El worker no está corriendo, o el nombre de la cola es incorrecto.
- Los jobs se reintentan siempre:
  - Revisa SMTP, reachability del BFF o payloads de plantilla inválidos.
- Los jobs llegan a `failed_jobs`:
  - Lee `last_error`, corrige la causa raíz y reencola solo después de verificar la corrección.
- Las campañas programadas no se despachan:
  - Confirma que el cron de `campaign:dispatch` está activo y que la hora del servidor es correcta.

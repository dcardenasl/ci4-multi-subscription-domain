<?php

declare(strict_types=1);

namespace Config;

trait NewsletterDomainServices
{
    public static function webhookSignatureService(bool $getShared = true): \App\Services\Newsletter\WebhookSignatureService
    {
        if ($getShared) {
            return static::getSharedInstance('webhookSignatureService');
        }
        return new \App\Services\Newsletter\WebhookSignatureService(config(\Config\NewsletterWebhooks::class));
    }
    public static function projectResponseMapper(bool $getShared = true): \dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface
    {
        if ($getShared) {
            return static::getSharedInstance('projectResponseMapper');
        }
        return new \dcardenasl\Ci4ApiCore\Mappers\DtoResponseMapper(\App\DTO\Response\Newsletter\ProjectResponseDTO::class);
    }
    public static function projectService(bool $getShared = true): \App\Interfaces\Newsletter\ProjectServiceInterface
    {
        if ($getShared) {
            return static::getSharedInstance('projectService');
        }
        return new \App\Services\Newsletter\ProjectService(new \dcardenasl\Ci4ApiCore\Repositories\GenericRepository(model(\App\Models\ProjectModel::class)), static::projectResponseMapper());
    }
    public static function subscriberResponseMapper(bool $getShared = true): \dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface
    {
        if ($getShared) {
            return static::getSharedInstance('subscriberResponseMapper');
        }
        return new \dcardenasl\Ci4ApiCore\Mappers\DtoResponseMapper(\App\DTO\Response\Newsletter\SubscriberResponseDTO::class);
    }
    public static function subscriberService(bool $getShared = true): \App\Interfaces\Newsletter\SubscriberServiceInterface
    {
        if ($getShared) {
            return static::getSharedInstance('subscriberService');
        }
        return new \App\Services\Newsletter\SubscriberService(new \dcardenasl\Ci4ApiCore\Repositories\GenericRepository(model(\App\Models\SubscriberModel::class)), static::subscriberResponseMapper());
    }
    public static function campaignResponseMapper(bool $getShared = true): \dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface
    {
        if ($getShared) {
            return static::getSharedInstance('campaignResponseMapper');
        }
        return new \dcardenasl\Ci4ApiCore\Mappers\DtoResponseMapper(\App\DTO\Response\Newsletter\CampaignResponseDTO::class);
    }
    public static function campaignService(bool $getShared = true): \App\Interfaces\Newsletter\CampaignServiceInterface
    {
        if ($getShared) {
            return static::getSharedInstance('campaignService');
        }
        return new \App\Services\Newsletter\CampaignService(new \dcardenasl\Ci4ApiCore\Repositories\GenericRepository(model(\App\Models\CampaignModel::class)), static::campaignResponseMapper());
    }
    public static function deliveryResponseMapper(bool $getShared = true): \dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface
    {
        if ($getShared) {
            return static::getSharedInstance('deliveryResponseMapper');
        }
        return new \dcardenasl\Ci4ApiCore\Mappers\DtoResponseMapper(\App\DTO\Response\Newsletter\DeliveryResponseDTO::class);
    }
    public static function deliveryService(bool $getShared = true): \App\Interfaces\Newsletter\DeliveryServiceInterface
    {
        if ($getShared) {
            return static::getSharedInstance('deliveryService');
        }
        return new \App\Services\Newsletter\DeliveryService(new \dcardenasl\Ci4ApiCore\Repositories\GenericRepository(model(\App\Models\DeliveryModel::class)), static::deliveryResponseMapper());
    }
    public static function emailTemplateResponseMapper(bool $getShared = true): \dcardenasl\Ci4ApiCore\Mappers\ResponseMapperInterface
    {
        if ($getShared) {
            return static::getSharedInstance('emailTemplateResponseMapper');
        }
        return new \dcardenasl\Ci4ApiCore\Mappers\DtoResponseMapper(\App\DTO\Response\Newsletter\EmailTemplateResponseDTO::class);
    }
    public static function emailTemplateService(bool $getShared = true): \App\Interfaces\Newsletter\EmailTemplateServiceInterface
    {
        if ($getShared) {
            return static::getSharedInstance('emailTemplateService');
        }
        return new \App\Services\Newsletter\EmailTemplateService(new \dcardenasl\Ci4ApiCore\Repositories\GenericRepository(model(\App\Models\EmailTemplateModel::class)), static::emailTemplateResponseMapper());
    }
    public static function landingAnalyticsService(bool $getShared = true): \App\Interfaces\Newsletter\LandingAnalyticsServiceInterface
    {
        if ($getShared) {
            return static::getSharedInstance('landingAnalyticsService');
        }
        return new \App\Services\Newsletter\LandingAnalyticsService(
            new \dcardenasl\Ci4ApiCore\Repositories\GenericRepository(model(\App\Models\ProjectModel::class)),
            new \dcardenasl\Ci4ApiCore\Repositories\GenericRepository(model(\App\Models\LandingAnalyticsSessionModel::class)),
            new \dcardenasl\Ci4ApiCore\Repositories\GenericRepository(model(\App\Models\LandingAnalyticsEventModel::class)),
            new \dcardenasl\Ci4ApiCore\Repositories\GenericRepository(model(\App\Models\LandingAnalyticsDailyAggregateModel::class)),
            config(\Config\LandingAnalytics::class)
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Newsletter;

use CodeIgniter\HTTP\ResponseInterface;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Exceptions\AuthorizationException;
use dcardenasl\Ci4ApiCore\Exceptions\ValidationException;
use dcardenasl\Ci4ApiCore\Http\ApiController;

class MediaController extends ApiController
{
    protected function resolveDefaultService(): object
    {
        return service('campaignService');
    }

    public function upload(): ResponseInterface
    {
        return $this->handleRequest(
            function (array $dto, SecurityContext $context): mixed {
                if (!$context->hasPermission('newsletter.campaigns.write')) {
                    throw new AuthorizationException(lang('Api.forbidden'));
                }

                $file = $this->request->getFile('image');
                if ($file === null || !$file->isValid()) {
                    throw new ValidationException(lang('Api.validationFailed'), ['image' => lang('Validation.media_upload_invalid_file')]);
                }

                // Validate it's an image
                if (!in_array($file->getMimeType(), ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true)) {
                    throw new ValidationException(lang('Api.validationFailed'), ['image' => lang('Validation.media_upload_invalid_type')]);
                }

                // Limit size (e.g. 5MB)
                if ($file->getSizeByUnit('mb') > 5) {
                    throw new ValidationException(lang('Api.validationFailed'), ['image' => lang('Validation.media_upload_size_limit')]);
                }

                $newName = $file->getRandomName();
                $publicPath = FCPATH . 'uploads/media/';

                if (!is_dir($publicPath)) {
                    mkdir($publicPath, 0755, true);
                }

                $file->move($publicPath, $newName);

                $url = base_url('uploads/media/' . $newName);

                return [
                    'ok' => true,
                    'url' => $url,
                ];
            }
        );
    }
}

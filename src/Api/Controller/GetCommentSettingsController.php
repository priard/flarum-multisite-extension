<?php

namespace PriArd\FlarumMultisite\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class GetCommentSettingsController extends AbstractShowController
{
    public $serializer = null; // We'll return raw data
    
    protected $settings;
    
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }
    
    protected function data(ServerRequestInterface $request, Document $document)
    {
        // Get character limits from settings
        $defaultLimit = (int) $this->settings->get('priard_multisite.default_character_limit', 5000);
        $characterLimits = json_decode(
            $this->settings->get('priard_multisite.character_limits', '{}'),
            true
        );
        
        // Get current tag from request if provided
        $tag = $request->getQueryParams()['tag'] ?? null;
        
        // Determine the applicable limit
        $currentLimit = $defaultLimit;
        if ($tag && isset($characterLimits[$tag])) {
            $currentLimit = (int) $characterLimits[$tag];
        }
        
        // Return settings as JSON response
        return [
            'characterLimit' => $currentLimit,
            'characterLimitByTag' => $characterLimits,
            'defaultLimit' => $defaultLimit,
            'settings' => [
                'allowMarkdown' => true,
                'allowMentions' => true,
                'minCommentLength' => 1,
                'maxCommentLength' => $currentLimit
            ]
        ];
    }
    
    public function handle(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $data = $this->data($request, new Document());
        
        return new \Laminas\Diactoros\Response\JsonResponse($data);
    }
}

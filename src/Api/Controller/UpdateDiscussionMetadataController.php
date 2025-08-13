<?php

namespace PriArd\FlarumMultisite\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Discussion\Discussion;
use PriArd\FlarumMultisite\Model\DiscussionMetadata;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Illuminate\Support\Arr;

class UpdateDiscussionMetadataController extends AbstractShowController
{
    public $serializer = null;
    
    protected function data(ServerRequestInterface $request, Document $document)
    {
        // Get discussion ID from URL
        $discussionId = Arr::get($request->getQueryParams(), 'id');
        
        // Verify discussion exists
        $discussion = Discussion::findOrFail($discussionId);
        
        // Get data from request body
        $body = $request->getParsedBody();
        
        // Validate and prepare metadata
        $metadata = [
            'discussion_id' => $discussionId,
            'source_domain' => Arr::get($body, 'domain'),
            'source_post_id' => Arr::get($body, 'postId'),
            'source_post_slug' => Arr::get($body, 'postSlug'),
            'source_post_url' => Arr::get($body, 'postUrl'),
            'site_tag' => Arr::get($body, 'siteTag')
        ];
        
        // Remove null values
        $metadata = array_filter($metadata, function ($value) {
            return $value !== null;
        });
        
        // Update or create metadata
        $discussionMetadata = DiscussionMetadata::updateForDiscussion($discussionId, $metadata);
        
        return [
            'success' => true,
            'discussionId' => $discussionId,
            'metadata' => $discussionMetadata->toArray()
        ];
    }
    
    public function handle(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        // Check for API token authentication
        $token = $request->getHeaderLine('Authorization');
        if (!$token || !str_starts_with($token, 'Token ')) {
            return new \Laminas\Diactoros\Response\JsonResponse(
                ['error' => 'Unauthorized'],
                401
            );
        }
        
        try {
            $data = $this->data($request, new Document());
            return new \Laminas\Diactoros\Response\JsonResponse($data);
        } catch (\Exception $e) {
            return new \Laminas\Diactoros\Response\JsonResponse(
                ['error' => $e->getMessage()],
                400
            );
        }
    }
}

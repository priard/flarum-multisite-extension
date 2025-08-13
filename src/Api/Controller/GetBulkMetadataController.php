<?php

namespace ITTechBlog\FlarumMultisite\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use PriArd\FlarumMultisite\Model\DiscussionMetadata;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Illuminate\Support\Arr;

class GetBulkMetadataController extends AbstractShowController
{
    public $serializer = null;
    
    protected function data(ServerRequestInterface $request, Document $document)
    {
        // Get discussion IDs from request body
        $body = $request->getParsedBody();
        $discussionIds = Arr::get($body, 'discussionIds', []);
        
        if (empty($discussionIds)) {
            return [
                'metadata' => []
            ];
        }
        
        // Limit to prevent abuse
        $discussionIds = array_slice($discussionIds, 0, 100);
        
        // Get metadata for all discussions
        $metadataCollection = DiscussionMetadata::getForDiscussions($discussionIds);
        
        // Format response
        $result = [];
        foreach ($metadataCollection as $metadata) {
            $result[$metadata->discussion_id] = [
                'sourceDomain' => $metadata->source_domain,
                'sourcePostId' => $metadata->source_post_id,
                'sourcePostSlug' => $metadata->source_post_slug,
                'sourcePostUrl' => $metadata->source_post_url,
                'siteTag' => $metadata->site_tag
            ];
        }
        
        return [
            'metadata' => $result
        ];
    }
    
    public function handle(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
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

<?php

namespace PriArd\FlarumMultisite\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use PriArd\FlarumMultisite\Model\DiscussionMetadata;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Illuminate\Support\Arr;

class GetDiscussionMetadataController extends AbstractShowController
{
    public $serializer = null;
    
    protected function data(ServerRequestInterface $request, Document $document)
    {
        // Get discussion ID from URL
        $discussionId = Arr::get($request->getQueryParams(), 'id');
        
        // Get metadata
        $metadata = DiscussionMetadata::where('discussion_id', $discussionId)->first();
        
        if (!$metadata) {
            return [
                'discussionId' => $discussionId,
                'metadata' => null
            ];
        }
        
        return [
            'discussionId' => $discussionId,
            'metadata' => [
                'sourceDomain' => $metadata->source_domain,
                'sourcePostId' => $metadata->source_post_id,
                'sourcePostSlug' => $metadata->source_post_slug,
                'sourcePostUrl' => $metadata->source_post_url,
                'siteTag' => $metadata->site_tag
            ]
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

<?php

namespace PriArd\FlarumMultisite\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Discussion\Discussion;
use PriArd\FlarumMultisite\Model\DiscussionMetadata;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Illuminate\Support\Arr;

class UpdateDiscussionStatusController extends AbstractShowController
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
        $action = Arr::get($body, 'action'); // 'lock', 'unlock', 'hide', 'restore'
        $postStatus = Arr::get($body, 'postStatus'); // WordPress post status
        
        $result = [];
        
        switch ($action) {
            case 'lock':
                // Lock the discussion (prevent new comments)
                $discussion->is_locked = true;
                $discussion->save();
                $result['locked'] = true;
                $result['message'] = 'Discussion locked successfully';
                break;
                
            case 'unlock':
                // Unlock the discussion
                $discussion->is_locked = false;
                $discussion->save();
                $result['locked'] = false;
                $result['message'] = 'Discussion unlocked successfully';
                break;
                
            case 'hide':
                // Hide the discussion from public view
                $discussion->hidden_at = new \DateTime();
                $discussion->save();
                $result['hidden'] = true;
                $result['message'] = 'Discussion hidden successfully';
                break;
                
            case 'restore':
                // Restore hidden discussion
                $discussion->hidden_at = null;
                $discussion->save();
                $result['hidden'] = false;
                $result['message'] = 'Discussion restored successfully';
                break;
                
            case 'auto':
                // Automatically decide based on WordPress post status
                if ($postStatus === 'publish') {
                    // Post is published - unlock and restore
                    $discussion->is_locked = false;
                    $discussion->hidden_at = null;
                    $discussion->save();
                    $result['locked'] = false;
                    $result['hidden'] = false;
                    $result['message'] = 'Discussion unlocked and visible';
                } elseif ($postStatus === 'draft' || $postStatus === 'pending') {
                    // Post is draft - lock but keep visible
                    $discussion->is_locked = true;
                    $discussion->save();
                    $result['locked'] = true;
                    $result['hidden'] = false;
                    $result['message'] = 'Discussion locked (post in draft)';
                } elseif ($postStatus === 'trash' || $postStatus === 'private') {
                    // Post is trashed or private - lock and hide
                    $discussion->is_locked = true;
                    $discussion->hidden_at = new \DateTime();
                    $discussion->save();
                    $result['locked'] = true;
                    $result['hidden'] = true;
                    $result['message'] = 'Discussion locked and hidden';
                }
                break;
                
            default:
                return [
                    'error' => 'Invalid action',
                    'discussionId' => $discussionId
                ];
        }
        
        // Update metadata if provided
        if ($postStatus) {
            $metadata = DiscussionMetadata::where('discussion_id', $discussionId)->first();
            if ($metadata) {
                $metadata->post_status = $postStatus;
                $metadata->save();
            }
        }
        
        $result['discussionId'] = $discussionId;
        $result['postStatus'] = $postStatus;
        
        return $result;
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
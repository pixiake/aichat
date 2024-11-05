<?php

namespace Pixiake\AiChat\Api\Controller;

use Flarum\Discussion\Discussion;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class MarkDiscussionController implements RequestHandlerInterface
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {   
        $actor = $request->getAttribute('actor');
        $data = $request->getParsedBody();

        $discussionId = Arr::get($data, 'discussionId', []);
        $discussion = Discussion::findOrFail($discussionId);
        
        // 权限检查
        if (!$actor->can('canMarkAnswer', $discussion)) {
            throw new PermissionDeniedException();
        }
        
        $mark = Arr::get($data, 'mark', []);  

        // 处理 needToLearn 标签
        if ($needToLearn = Arr::get($mark, 'needToLearn')) {
            $tagIds = Arr::get($needToLearn, 'ids', []);
            $action = Arr::get($needToLearn, 'action');
            
            // 如果没有标签，直接返回
            if (empty($tagIds)) {
                return new JsonResponse(null, 204);
            }
            
            $tagId = $tagIds[0];


            if ($action === 'add') {
                try {
                    // 检查标签是否已存在
                    if (!$discussion->tags()->where('tags.id', $tagId)->exists()) {
                        $discussion->tags()->attach($tagId);
                    } 
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'errors' => [
                            ['detail' => 'action falied: ' . $e->getMessage()]
                        ]
                    ], 500);
                }
                
            } elseif ($action === 'remove') {
                try {
                    // 检查标签是否已存在
                    if ($discussion->tags()->where('tags.id', $tagId)->exists()) {
                        $discussion->tags()->detach($tagIds);
                    } 
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'errors' => [
                            ['detail' => 'action falied: ' . $e->getMessage()]
                        ]
                    ], 500);
                }
            }
        }

        // 处理 learned 标签
        if ($learned = Arr::get($mark, 'learned')) {
            $tagIds = Arr::get($learned, 'ids', []);
            $action = Arr::get($learned, 'action');
            
            // 如果没有标签，直接返回
            if (empty($tagIds)) {
                return new JsonResponse(null, 204);
            }
            
            $tagId = $tagIds[0];

            if ($action === 'add') {
                try {
                    // 检查标签是否已存在
                    if (!$discussion->tags()->where('tags.id', $tagId)->exists()) {
                        $discussion->tags()->attach($tagId);
                    } 
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'errors' => [
                            ['detail' => 'action falied: ' . $e->getMessage()]
                        ]
                    ], 500);
                }
            } elseif ($action === 'remove') {
                try {
                    // 检查标签是否已存在
                    if ($discussion->tags()->where('tags.id', $tagId)->exists()) {
                        $discussion->tags()->detach($tagIds);
                    } 
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'errors' => [
                            ['detail' => 'action falied: ' . $e->getMessage()]
                        ]
                    ], 500);
                }
            }
        }
         
        return new JsonResponse(null, 204);
    }
}
<?php

namespace Pixiake\AiChat\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Http\RequestUtil;
use Flarum\Post\Post;
use Illuminate\Support\Arr;
use Flarum\Api\Serializer\PostSerializer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Pixiake\AiChat\AiChatClient;

class MarkPostController implements RequestHandlerInterface
{
    public function __construct(
        AiChatClient $client
    ) {
        $this->client = $client;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        $postId = Arr::get($request->getParsedBody(), 'postId');
        $isCorrect = Arr::get($request->getParsedBody(), 'isCorrect');
        $isWrong = Arr::get($request->getParsedBody(), 'isWrong');

        $post = Post::findOrFail($postId);
        
        // 权限检查
        if (!$actor->can('canMarkAnswer', $post)) {
            throw new PermissionDeniedException();
        }


        $post->is_marked_correct = $isCorrect;
        $post->is_marked_wrong = $isWrong;
        $post->save();
         
        if ($isCorrect) {
            $this->client->self_learning($post->discussion_id, $post->content, "upload");
        } 

        if ($isWrong) {
            $this->client->self_learning($post->discussion_id, $post->content, "delete");
        }
      

        return new JsonResponse(null, 204);
    }
}
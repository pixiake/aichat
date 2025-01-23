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
use Flarum\Settings\SettingsRepositoryInterface;

class MarkPostController implements RequestHandlerInterface
{
    public function __construct(
        AiChatClient $client,
        SettingsRepositoryInterface $settings
    ) {
        $this->client = $client;
        $this->settings = $settings;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        $postId = Arr::get($request->getParsedBody(), 'postId');
        $isCorrect = Arr::get($request->getParsedBody(), 'isCorrect') || false;
        $isWrong = Arr::get($request->getParsedBody(), 'isWrong') || false;

        $post = Post::findOrFail($postId);
        
        // 权限检查
        if (!$actor->can('canMarkAnswer', $post)) {
            throw new PermissionDeniedException();
        }


        $post->is_marked_correct = $isCorrect;
        $post->is_marked_wrong = $isWrong;
        $post->save();
         
        if ($isWrong) {
            // 获取讨论帖链接
            $flarumUrl = $this->settings->get('pixiake-aichat.url_for_flarum', '');
            $url = $flarumUrl . sprintf("%d", $post -> discussion -> id);
    
            $title = $post -> discussion -> title;
    
            $messages = [
                'msgtype' => 'markdown',
                'markdown' => [
                    'content' => "[{$title}]({$url})\n\n哪位热心大佬可以帮忙看看这个问题，感谢~~"
                ]
            ];
    
            $this -> client -> webhook($messages);
        }


        // if ($isCorrect) {
        //     $this->client->self_learning($post->discussion_id, $post->content, "upload");
        // } 

        // if ($isWrong) {
        //     $this->client->self_learning($post->discussion_id, $post->content, "delete");
        // }
      

        return new JsonResponse(null, 204);
    }
}
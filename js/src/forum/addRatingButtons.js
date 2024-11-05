import { extend } from 'flarum/common/extend';
import CommentPost from 'flarum/forum/components/CommentPost';
import Button from 'flarum/common/components/Button';


export default function addRatingButtons() {
  // 添加标记到帖子顶部
  extend(CommentPost.prototype, 'headerItems', function(items) {
    const post = this.attrs.post;
    const isMarkedCorrect = post.attribute('isMarkedCorrect');
    const isMarkedWrong = post.attribute('isMarkedWrong');

    if (isMarkedCorrect || isMarkedWrong) {
      items.add('answerMark',
        <div className={`AnswerMark ${isMarkedCorrect ? 'correct' : 'wrong'}`}>
          <i className={isMarkedCorrect ? 'fas fa-check' : 'fas fa-times'}></i>
          <span>{isMarkedCorrect ? app.translator.trans('pixiake-aichat.forum.correct_answer') : app.translator.trans('pixiake-aichat.forum.wrong_answer')}</span>
        </div>,
        100
      );
    }
  });

  extend(CommentPost.prototype, 'actionItems', function(items) {
    const post = this.attrs.post;
    const isMarkedCorrect = post.attribute('isMarkedCorrect');
    const isMarkedWrong = post.attribute('isMarkedWrong');
    
    // 检查权限
    // if (!discussion.canMarkAnswer()) {
    //   return;
    // }

    // 检查是否是机器人的回复
    const botUserId = app.forum.attribute('botUserId');
    const discussion = post.discussion();
    const needToLearnTags = app.forum.attribute('needLearnTags');
    const learnedTags = app.forum.attribute('learnedTags');

    if (post.user() && post.user().id() === botUserId) {
      if (app.session.user && (app.session.user.isAdmin() || app.session.user.hasPermission('discussion.moderate'))) {
        items.add('markCorrect',
          Button.component({
            className: `Button Button--link ${isMarkedCorrect ? 'marked-correct' : ''}`,
            onclick: () => {
              // 更新前端状态
              post.pushAttributes({
                isMarkedCorrect: !isMarkedCorrect,
                isMarkedWrong: false
              });

              app.request({
                method: 'POST',
                url: app.forum.attribute('apiUrl') + '/ai-chat/v1alpha1/mark-post',
                body: {
                  postId: post.id(),
                  isCorrect: !isMarkedCorrect,
                  isWrong: false
                }
              }).catch(error => {
                  app.alerts.show({ type: 'error' }, error.message);
              });

              app.request({
                method: 'POST',
                url: app.forum.attribute('apiUrl') + '/ai-chat/v1alpha1/mark-discussion',
                body: {
                  discussionId: discussion.id(),
                  mark: {
                    learned: {
                      ids: learnedTags,
                      action: !isMarkedCorrect ? 'add' : ''
                    },
                    needToLearn: {
                      ids: needToLearnTags,
                      action: !isMarkedCorrect ? 'remove' : ''
                    }
                  }
                }
              }).catch(error => {
                  app.alerts.show({ type: 'error' }, error.message);
              });
            },
            icon: isMarkedCorrect ? 'fas fa-check-circle' : 'far fa-check-circle'
          }, isMarkedCorrect ?  app.translator.trans('pixiake-aichat.forum.unmark_correct') : app.translator.trans('pixiake-aichat.forum.mark_correct')),
          -10
        );
  
        // 错误答案按钮
        items.add('markWrong',
          Button.component({
            className: `Button Button--link ${isMarkedWrong ? 'marked-wrong' : ''}`,
            onclick: () => {
              // 更新前端状态
              post.pushAttributes({
                isMarkedCorrect: false,
                isMarkedWrong: !isMarkedWrong
              });

              app.request({
                method: 'POST',
                url: app.forum.attribute('apiUrl') + '/ai-chat/v1alpha1/mark-post',
                body: {
                  postId: post.id(),
                  isCorrect: false,
                  isWrong: !isMarkedWrong
                }
              }).catch(error => {
                  app.alerts.show({ type: 'error' }, error.message);
              });

              app.request({
                method: 'POST',
                url: app.forum.attribute('apiUrl') + '/ai-chat/v1alpha1/mark-discussion',
                body: {
                  discussionId: discussion.id(),
                  mark: {
                    needToLearn: {
                      ids: needToLearnTags,
                      action: !isMarkedWrong ? 'add' : ''
                    },
                    learned: {
                      ids: learnedTags,
                      action: !isMarkedWrong ? 'remove' : ''
                    }
                  },
                }
              }).catch(error => {
                  app.alerts.show({ type: 'error' }, error.message);
              });
            },
            icon: isMarkedWrong ? 'fas fa-times-circle' : 'far fa-times-circle'
          }, isMarkedWrong ? app.translator.trans('pixiake-aichat.forum.unmark_wrong') : app.translator.trans('pixiake-aichat.forum.mark_wrong')),
          -9
        );
      }
    }
  });
}
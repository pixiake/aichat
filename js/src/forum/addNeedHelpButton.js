import { extend } from 'flarum/common/extend';
import CommentPost from 'flarum/forum/components/CommentPost';
import Button from 'flarum/common/components/Button';


export default function addNeedHelpButton() {
    extend(CommentPost.prototype, 'actionItems', function(items) {
        const post = this.attrs.post;
        const discussion = post.discussion();
        const isMarkedWrong = post.attribute('isMarkedWrong');
        const needHelpTags = app.forum.attribute('needHelpTags');
        // 检查是否是机器人的回复
        const botUserId = app.forum.attribute('botUserId');
        if (post.user() && post.user().id() !== botUserId) {
            return;
        }

        // // 检查当前 disscussion 是否包含 needHelp 标签
        // const needHelpTags = app.forum.attribute('needHelpTags') || [];
        // const tags = discussion.tags();
        // const hasNeedHelpTag = tags && tags.some(tag => needHelpTags.includes(tag.id()));
        // console.log(post)
         
        // if (hasNeedHelpTag) {
        //     post.pushAttributes({
        //         isMarkedWrong: hasNeedHelpTag
        //       });
        // }

        items.add('needHelp',
            Button.component({
                className: 'Button Button--link',
                icon: isMarkedWrong ? 'fas fa-running' : 'fas fa-hands-helping',
                onclick: () => {
                    // 更新前端状态
                    if (!isMarkedWrong) {
                        post.pushAttributes({
                            isMarkedWrong: true
                          });
                        app.request({
                          method: 'POST',
                          url: app.forum.attribute('apiUrl') + '/ai-chat/v1alpha1/mark-post',
                          body: {
                              postId: post.id(),
                              isWrong: true
                          }
                        }).catch(error => {
                            // 回滚前端状态
                            post.pushAttributes({
                                isMarkedWrong
                            });
                        });

                        app.request({
                            method: 'POST',
                            url: app.forum.attribute('apiUrl') + '/ai-chat/v1alpha1/mark-discussion',
                            body: {
                              discussionId: discussion.id(),
                              mark: {
                                needToLearn: {
                                  ids: needHelpTags,
                                  action: "add"
                                }
                              }
                            }
                          }).catch(error => {
                              app.alerts.show({ type: 'error' }, error.message);
                          });
                    }
                }
            }, isMarkedWrong ? '热心大佬正在赶来...' : '呼叫热心大佬支援',
            -9
        )
        );
    });
}
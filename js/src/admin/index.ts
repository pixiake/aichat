import app from 'flarum/admin/app';
import AiChatSettings from "./components/AiChatSettings";

app.initializers.add('pixiake/aichat', () => {
  console.log('[pixiake/aichat] Hello, admin!');
  app.extensionData
    .for('pixiake-aichat')
    .registerPermission(
      {
         icon: 'fas fa-times',
         label: app.translator.trans('pixiake-aichat.admin.permissions.mark_answer_label'),
         permission: 'discussion.markAnswer'
      }, 
    'moderate'
    )
    .registerPage(AiChatSettings);
});

import app from 'flarum/admin/app';
import AiChatSettings from "./components/AiChatSettings";

app.initializers.add('pixiake/aichat', () => {
  console.log('[pixiake/aichat] Hello, admin!');
  app.extensionData
    .for('pixiake-aichat')
    .registerPermission(
      {
        label: app.translator.trans('pixiake-aichat.admin.permissions.use_ai_chat_assistant_label'),
        icon: 'fas fa-comment',
        permission: 'discussion.useAiChatAssistant',
        allowGuest: true,
      },
      'start'
    )
    .registerPage(AiChatSettings);
});

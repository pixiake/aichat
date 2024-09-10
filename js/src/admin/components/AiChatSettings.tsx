import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';

export default class AiChatSettings extends ExtensionPage {
  content() {
    return (
      <div className="ExtensionPage-settings">
        <div className="container">
          <div className="Form">
            {this.buildSettingComponent({
              setting: 'pixiake-aichat.url_for_flarum',
              type: 'text',
              label: app.translator.trans('pixiake-aichat.admin.settings.url_for_flarum_label'),
              help: app.translator.trans('pixiake-aichat.admin.settings.url_for_flarum_help', {
                a: <a href="https://platform.openai.com/account/api-keys" target="_blank" rel="noopener" />,
              }),
              placeholder: 'http://...',
            })}
            {this.buildSettingComponent({
              setting: 'pixiake-aichat.api_key_for_flarum',
              type: 'text',
              label: app.translator.trans('pixiake-aichat.admin.settings.api_key_for_flarum_label'),
              help: app.translator.trans('pixiake-aichat.admin.settings.api_key_for_flarum_help'),
            })}
            {this.buildSettingComponent({
              setting: 'pixiake-aichat.async_server_url',
              type: 'text',
              label: app.translator.trans('pixiake-aichat.admin.settings.async_server_url_label'),
              help: app.translator.trans('pixiake-aichat.admin.settings.async_server_url_help'),
              placeholder: 'http://...',
            })}
            {this.buildSettingComponent({
              setting: 'pixiake-aichat.apiserver_url_for_chatbot',
              type: 'text',
              label: app.translator.trans('pixiake-aichat.admin.settings.apiserver_url_for_chatbot_label'),
              help: app.translator.trans('pixiake-aichat.admin.settings.apiserver_url_for_chatbot_help'),
              placeholder: 'http://...',
            })}
            {this.buildSettingComponent({
              setting: 'pixiake-aichat.api_key_for_chatbot',
              type: 'text',
              label: app.translator.trans('pixiake-aichat.admin.settings.api_key_for_chatbot_label'),
              help: app.translator.trans('pixiake-aichat.admin.settings.api_key_for_chatbot_help'),
              placeholder: 'sk-...',
            })}
            {this.buildSettingComponent({
              setting: 'pixiake-aichat.max_tokens',
              type: 'number',
              label: app.translator.trans('pixiake-aichat.admin.settings.max_tokens_label'),
              help: app.translator.trans('pixiake-aichat.admin.settings.max_tokens_help', {
                a: <a href="https://help.openai.com/en/articles/4936856" target="_blank" rel="noopener" />,
              }),
              default: 100,
            })}
            {this.buildSettingComponent({
              setting: 'pixiake-aichat.user_for_answer',
              type: 'number',
              label: app.translator.trans('pixiake-aichat.admin.settings.user_for_answer_label'),
              help: app.translator.trans('pixiake-aichat.admin.settings.user_for_answer_help'),
            })}
            {this.buildSettingComponent({
              setting: 'pixiake-aichat.user_for_chatbot',
              type: 'text',
              label: app.translator.trans('pixiake-aichat.admin.settings.user_for_chatbot_label'),
              help: app.translator.trans('pixiake-aichat.admin.settings.user_for_chatbot_help'),
            })}
            {this.buildSettingComponent({
              setting: 'pixiake-aichat.enable_on_discussion_started',
              type: 'boolean',
              label: app.translator.trans('pixiake-aichat.admin.settings.enable_on_discussion_started_label'),
              help: app.translator.trans('pixiake-aichat.admin.settings.enable_on_discussion_started_help'),
            })}
            {this.buildSettingComponent({
              type: 'flarum-tags.select-tags',
              setting: 'pixiake-aichat.enabled-tags',
              label: app.translator.trans('pixiake-aichat.admin.settings.enabled_tags_label'),
              help: app.translator.trans('pixiake-aichat.admin.settings.enabled_tags_help'),
              options: {
                requireParentTag: false,
                limits: {
                  max: {
                    secondary: 0,
                  },
                },
              },
            })}
            <div className="Form-group">{this.submitButton()}</div>
          </div>
        </div>
      </div>
    );
  }
}

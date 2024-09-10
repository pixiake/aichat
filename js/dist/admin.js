(()=>{var t={n:a=>{var e=a&&a.__esModule?()=>a.default:()=>a;return t.d(e,{a:e}),e},d:(a,e)=>{for(var i in e)t.o(e,i)&&!t.o(a,i)&&Object.defineProperty(a,i,{enumerable:!0,get:e[i]})},o:(t,a)=>Object.prototype.hasOwnProperty.call(t,a)};(()=>{"use strict";const a=flarum.core.compat["common/app"];t.n(a)().initializers.add("pixiake/aichat",(function(){console.log("[pixiake/aichat] Hello, forum and admin!")}));const e=flarum.core.compat["admin/app"];var i=t.n(e);function n(t,a){return n=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(t,a){return t.__proto__=a,t},n(t,a)}const s=flarum.core.compat["admin/components/ExtensionPage"];var r=function(t){function a(){return t.apply(this,arguments)||this}var e,s;return s=t,(e=a).prototype=Object.create(s.prototype),e.prototype.constructor=e,n(e,s),a.prototype.content=function(){return m("div",{className:"ExtensionPage-settings"},m("div",{className:"container"},m("div",{className:"Form"},this.buildSettingComponent({setting:"pixiake-aichat.url_for_flarum",type:"text",label:i().translator.trans("pixiake-aichat.admin.settings.url_for_flarum_label"),help:i().translator.trans("pixiake-aichat.admin.settings.url_for_flarum_help",{a:m("a",{href:"https://platform.openai.com/account/api-keys",target:"_blank",rel:"noopener"})}),placeholder:"http://..."}),this.buildSettingComponent({setting:"pixiake-aichat.api_key_for_flarum",type:"text",label:i().translator.trans("pixiake-aichat.admin.settings.api_key_for_flarum_label"),help:i().translator.trans("pixiake-aichat.admin.settings.api_key_for_flarum_help")}),this.buildSettingComponent({setting:"pixiake-aichat.async_server_url",type:"text",label:i().translator.trans("pixiake-aichat.admin.settings.async_server_url_label"),help:i().translator.trans("pixiake-aichat.admin.settings.async_server_url_help"),placeholder:"http://..."}),this.buildSettingComponent({setting:"pixiake-aichat.apiserver_url_for_chatbot",type:"text",label:i().translator.trans("pixiake-aichat.admin.settings.apiserver_url_for_chatbot_label"),help:i().translator.trans("pixiake-aichat.admin.settings.apiserver_url_for_chatbot_help"),placeholder:"http://..."}),this.buildSettingComponent({setting:"pixiake-aichat.api_key_for_chatbot",type:"text",label:i().translator.trans("pixiake-aichat.admin.settings.api_key_for_chatbot_label"),help:i().translator.trans("pixiake-aichat.admin.settings.api_key_for_chatbot_help"),placeholder:"sk-..."}),this.buildSettingComponent({setting:"pixiake-aichat.max_tokens",type:"number",label:i().translator.trans("pixiake-aichat.admin.settings.max_tokens_label"),help:i().translator.trans("pixiake-aichat.admin.settings.max_tokens_help",{a:m("a",{href:"https://help.openai.com/en/articles/4936856",target:"_blank",rel:"noopener"})}),default:100}),this.buildSettingComponent({setting:"pixiake-aichat.user_for_answer",type:"number",label:i().translator.trans("pixiake-aichat.admin.settings.user_for_answer_label"),help:i().translator.trans("pixiake-aichat.admin.settings.user_for_answer_help")}),this.buildSettingComponent({setting:"pixiake-aichat.user_for_chatbot",type:"text",label:i().translator.trans("pixiake-aichat.admin.settings.user_for_chatbot_label"),help:i().translator.trans("pixiake-aichat.admin.settings.user_for_chatbot_help")}),this.buildSettingComponent({setting:"pixiake-aichat.enable_on_discussion_started",type:"boolean",label:i().translator.trans("pixiake-aichat.admin.settings.enable_on_discussion_started_label"),help:i().translator.trans("pixiake-aichat.admin.settings.enable_on_discussion_started_help")}),this.buildSettingComponent({type:"flarum-tags.select-tags",setting:"pixiake-aichat.enabled-tags",label:i().translator.trans("pixiake-aichat.admin.settings.enabled_tags_label"),help:i().translator.trans("pixiake-aichat.admin.settings.enabled_tags_help"),options:{requireParentTag:!1,limits:{max:{secondary:0}}}}),m("div",{className:"Form-group"},this.submitButton()))))},a}(t.n(s)());i().initializers.add("pixiake/aichat",(function(){console.log("[pixiake/aichat] Hello, admin!"),i().extensionData.for("pixiake-aichat").registerPermission({label:i().translator.trans("pixiake-aichat.admin.permissions.use_ai_chat_assistant_label"),icon:"fas fa-comment",permission:"discussion.useAiChatAssistant",allowGuest:!0},"start").registerPage(r)}))})(),module.exports={}})();
//# sourceMappingURL=admin.js.map
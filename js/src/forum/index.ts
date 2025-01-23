import app from 'flarum/forum/app';
import addNeedHelpButton from './addNeedHelpButton';
// import addRatingButtons from './addRatingButtons';

app.initializers.add('pixiake/aichat', () => {
  addNeedHelpButton();
});

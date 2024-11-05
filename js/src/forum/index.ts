import app from 'flarum/forum/app';
import addRatingButtons from './addRatingButtons';

app.initializers.add('pixiake/aichat', () => {
  addRatingButtons();
});

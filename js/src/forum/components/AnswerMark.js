import Component from 'flarum/Component';

export default class AnswerMark extends Component {
  view() {
    const post = this.attrs.post;
    
    if (!post.isMarkedCorrect() && !post.isMarkedWrong()) {
      return null;
    }

    const isCorrect = post.isMarkedCorrect();
    
    return (
      <div className={`AnswerMark ${isCorrect ? 'correct' : 'wrong'}`}>
        <i className={isCorrect ? 'fas fa-check' : 'fas fa-times'}></i>
        <span>{isCorrect ? '正确答案' : '错误答案'}</span>
      </div>
    );
  }
}
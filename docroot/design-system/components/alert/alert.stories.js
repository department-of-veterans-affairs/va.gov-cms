import alert from './alert.twig';
import './alert.css';
import './alert.js';
import DrupalAttribute from '../../DrupalAttribute';

export default { title: 'Components/Alerts' }

export const Warning = (_, { loaded: { component } }) => component;

Warning.args = {
  attributes: new DrupalAttribute(),
  type: 'warning',
  title_ids: ['status', 'warning', 'error'],
  status_headings: ['status', 'warning', 'error'],
  messages: ['this is an example alert message']
}

Warning.render = async args => {
  return await alert({
    ...Warning.args
  });
}

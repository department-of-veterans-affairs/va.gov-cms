import alert from './alert.twig';
import './alert.css';
import './alert.js';
import drupalAttribute from 'drupal-attribute';

export default { title: 'Alerts' }

export const Warning = (_, { loaded: { component } }) => component;

Warning.args = {
  attributes: new drupalAttribute(),
  type: 'warning',
  title_ids: ['status', 'warning', 'error'],
  status_headings: ['status', 'warning', 'error'],
  messages: ['this is an example alert message']
}

Warning.render = async args => {
  return await alert({
    attributes: args.attribute,
    type: args.type,
    title_ids: args.title_ids,
    status_headings: args.status_headings,
    messages: args.messages,
  });
}

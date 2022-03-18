import alert from './alert.twig';
import './alert.scss';
import './index.js';
import DrupalAttribute from '../../DrupalAttribute';

export default { title: 'Components/Alerts' }

export const Status = (_, { loaded: { component } }) => component;

Status.args = {
  attributes: new DrupalAttribute(),
  type: 'status',
  title_ids: ['status', 'warning', 'error'],
  status_headings: ['status', 'warning', 'error'],
  messages: ['this is an example alert message']
}

Status.render = async args => {
  return await alert({
    ...Status.args, ...args
  });
}

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
    ...Warning.args, ...args
  });
}

export const Error = (_, { loaded: { component } }) => component;

Error.args = {
  attributes: new DrupalAttribute(),
  type: 'error',
  title_ids: ['status', 'warning', 'error'],
  status_headings: ['status', 'warning', 'error'],
  messages: ['this is an example alert message']
}

Error.render = async args => {
  return await alert({
    ...Error.args, ...args
  });
}

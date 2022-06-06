// fontawesome api methods
import { library, dom } from '@fortawesome/fontawesome-svg-core';
// import specific icons as needed to keep final bundle small. fa icon name in comment.
import {
  faBan, // ban
  faBullhorn, // bullhorn
  faCheck, // check
  faChevronDown, // chevron-down
  faCircleInfo, // circle-info
  faClone, // clone
  faEllipsisVertical, // ellipsis-vertical
  faMagnifyingGlass, // magnifying-glass
  faPencil, // pencil
  faPlus, // plus
  faThumbsDown, // thumbs down
  faThumbsUp, // trash
  faTrash, // trash
  faTriangleExclamation // warning
} from '@fortawesome/free-solid-svg-icons';

// Import module styles
import './icon.scss';

// Import module template
import './icon.twig';

// Add icons to our library.
library.add(
  faBan,
  faBullhorn,
  faCheck,
  faChevronDown,
  faCircleInfo,
  faClone,
  faEllipsisVertical,
  faMagnifyingGlass,
  faPencil,
  faPlus,
  faThumbsDown,
  faThumbsUp,
  faTrash,
  faTriangleExclamation
);

// Automatically replace <i> elements with <svg>.
dom.watch();

export const name = 'icon';

export function disable() {}

export function enable() {}

export default enable;

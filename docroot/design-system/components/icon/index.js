// fontawesome api methods
import { library, dom } from '@fortawesome/fontawesome-svg-core';
// import specific icons as needed to keep bundle small
import { faChevronDown, faMagnifyingGlass, faTriangleExclamation} from '@fortawesome/free-solid-svg-icons';

// Import module styles
import './icon.scss';

// Import module template
import './icon.twig';

// Add icons to our library.
library.add(
  faChevronDown,
  faMagnifyingGlass,
  faTriangleExclamation
);

// Automatically replace <i> elements with <svg>.
dom.watch();

export const name = 'icon';

export function disable() {}

export function enable() {}

export default enable;

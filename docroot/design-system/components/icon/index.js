// fontawesome api methods
import { library, dom } from '@fortawesome/fontawesome-svg-core';
// import specific icons as needed to keep bundle small
import { faChevronDown, faMagnifyingGlass, faTriangleExclamation} from '@fortawesome/free-solid-svg-icons';

// Import module styles
import './icon.scss';

// Import module template
import './icon.twig';

library.add(faChevronDown, faMagnifyingGlass, faTriangleExclamation);

dom.watch();

export const name = 'icon';

export function disable() {}

export function enable() {}

export default enable;

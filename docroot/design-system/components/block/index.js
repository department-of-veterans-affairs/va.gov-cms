// Import module styles
import './block.css';

// Import module template
import './block.twig';

export const name = 'block';

export function disable() {}

export function enable() {
  console.log('javascript works!')
}

export default enable;

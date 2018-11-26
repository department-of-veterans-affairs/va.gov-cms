# UI Component List
A master list of UI components & patterns we'll be using repeatedly. This project uses Atomic Design principles
to organize components. More info about this manner of organization can be found [here](http://atomicdesign.bradfrost.com/chapter-2/).


## Atoms
Building blocks of all user interfaces. Basic UI elements that can't be broken down any further.
1. Headings
2. Icons

## Molecules
Simple groups of UI elements that work together as a unit.
1. Breadcrumbs
2. Buttons
3. Last updated text
4. Intro text
5. Expandable text

## Organisms
Complex UI components made up of molecules and atoms and other organisms.
1. Left rail navigation
2. Right rail content
3. Graybox
4. Callout
4. Accordions
5. Subway Map
6. Featured
7. Alerts
8. Starred Horizontal Rule
9. Header


### To add additional UI components:

1. Determine whether the component you want to add is an *atom*, *molecule*, or *organism*.
2. Create a new folder for it in the directory that corresponds to it's component type. The folder name should `pattern` followed by the name of the component:
`pattern-componentname`.
3. At minimum, you need to create a Twig template `pattern-componentname.html.twig` and a YML file `pattern-componentname.ui_patterns.yml` in order to register
your new component.
4. If this component has specific styles or javascript add them with `_pattern-componentname.scss` and `pattern-componentname.js`. Don't forget to list them in the
libraries key of the component's YML!

There's an example component in the `pattern-example` folder in this directory. Check it out.




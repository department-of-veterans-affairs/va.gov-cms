# Environments: Codespaces

## About

[Github Codespaces](https://github.com/features/codespaces) is a cloud-based development environment that can be used in-browser or (preferably) with [Visual Studio Code](https://code.visualstudio.com). Please join [#codespaces](https://dsva.slack.com/archives/C01AN96U39V) for any comments or questions about using Codespaces at VA .

## Access

1. Request access to the beta by [creating a Github issue here](https://github.com/department-of-veterans-affairs/github-user-requests/issues/new?assignees=&labels=general+issue%2C+user-created&template=general-help-request.md&title=Add%20user%20to%20Codespaces).

## Web UI

1. Visit 
2. Click green "Code" button
3. Select "Open with Codespaces"
4. See [Getting Started](https://github.com/department-of-veterans-affairs/vets-website/blob/master/docs/GithubCodespaces.md#getting-started) in `vets-website` for more detail.

## Local VS Code
1. Download and install [Visual Studio Code](https://code.visualstudio.com/download)
  1. Arch Linux: `yay --sync visual-studio-code-bin`
1. Open VS Code and install the [Codespaces plugin](https://marketplace.visualstudio.com/items?itemName=ms-vsonline.vsonline)
  ![codespaces plugin](https://user-images.githubusercontent.com/101649/111006584-4d24ad80-834a-11eb-84d8-b0f574880e49.png)
1. Once you've received access, go to the main [Codespaces page](https://github.com/codespaces) and click the 'New codespace' button
1. Make sure your remote fork is updated with the latest in upstream repo (must have .devenvironment to work)
1. Type in the name of your fork of the [CMS repo](https://github.com/department-of-veterans-affairs/va.gov-cms) (create one if you haven't already) and choose the master branch. If it doesn't come up in the list you may have to type the full name, e.g. `elijahlynn/va.gov-cms`, not just `va.gov-cms`:
  ![codespaces creation](https://user-images.githubusercontent.com/101649/111007305-beb12b80-834b-11eb-8c80-138586ca4720.png)
1. Click "Sign into GitHub", a browser will open authorizing GitHub and VS Code.
   ![image](https://user-images.githubusercontent.com/1504756/111011691-0d13f980-834f-11eb-8595-cff579659869.png)
1. Click 'Create codespace'
1. You will see this dialog:

<img width="652" alt="Screen Shot 2021-08-24 at 4 24 37 PM" src="https://user-images.githubusercontent.com/101649/130698169-741b0154-28b9-4b38-80d3-ed507225a8b9.png">

1. Choose a machine type with at least 64GB of disk space to ensure that you have enough space to run the project.
1. Open VS Code, click the 'Remote Explorer' tab on the left, and click the 'Connect to codespace' button (it looks like an electric plug) to choose the codespace you just created:
   ![codespaces connection](https://user-images.githubusercontent.com/101649/111007602-75151080-834c-11eb-8c5d-9ef73ef03b30.png)
1. After a few seconds, you will be connected to codespaces, and the IDE will function like it is running locally for all intents and purposes
1. The development environment will automatically configure, install and start [lando](lando.md) on creation, which takes ~10-15 minutes. To monitor the process, choose 'New Terminal' from the 'Terminal' menu, and run this command: `tail -f ~/post-create.log`. The environment configuration also suggests plugins for linting and code style checking, and sets up the upstream git remote for the main CMS repo.
1. When the setup process is complete, you will see the text: `File sync from cms-prod-files-latest.tgz is complete.`
1. 1. You may want to change your git email address for commits with `git config --global user.email first.last@workemail.com`. You can see what is currently listed with `git config --global --list`. 
1. Your development environment is ready to use! Create a new terminal and run the command `lando info`. Mouse over the link to localhost, and VS Code will provide instructions to open the site in your browser with automatic port forwarding:
  ![starting lando](https://user-images.githubusercontent.com/101649/111008775-156c3480-834f-11eb-878d-10a665a777d4.png)

## Customization

### Shell environment

To set up your preferred shell environment, you may create a public 'dotfiles' repo under your own github account. More information is available in [the official documentation](https://docs.github.com/en/github/developing-online-with-codespaces/personalizing-codespaces-for-your-account). If you create and populate a `.bash_aliases` file in your dotfiles repository and [enable your dotfiles repository for Codespaces](https://docs.github.com/en/codespaces/customizing-your-codespace/personalizing-codespaces-for-your-account#enabling-your-dotfiles-repository-for-codespaces), any aliases you add will be available in the codespaces environment.

## Usage

### Viewing the Drupal site

1. In a terminal, run the command `lando info`
2. In the `appserver` section of the output, hover over one of the localhost addresses, e.g. `http://localhost:49161`
3. Command-click to automatically forward the port and open in your local browser

### Debugging

To debug with [Xdebug](https://xdebug.org), follow these steps:

1. In a terminal, run the command `lando xdebug-on`
2. Click the debugging tab on the left, and then the green play button to the left of 'Listen for Xdebug':
  ![starting xdebug](https://user-images.githubusercontent.com/101649/111009307-8102d180-8350-11eb-8a59-70d7270d0ea6.png)
3. Set a breakpoint and load a page in your browser. Go to town!
  ![xdebug](https://user-images.githubusercontent.com/101649/111009487-fbcbec80-8350-11eb-8057-afc1c86f05e4.jpg)

## Global Configuration

The codespaces configuration and setup script are located in the [.devcontainer](../.devcontainer) directory of this repo. The main configuration is stored in `devcontainer.json` - available properties are [documented here](https://code.visualstudio.com/docs/remote/devcontainerjson-reference).

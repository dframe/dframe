git subsplit init https://github.com/dframe/dframe.git
git subsplit publish --heads="master v4.1" src/Asset:https://github.com/dframe/asset.git
git subsplit publish --heads="master v4.1" src/Config:https://github.com/dframe/config.git
git subsplit publish --heads="master v4.1" src/Console:https://github.com/dframe/Console.git
git subsplit publish --heads="master v4.1" src/Cron:https://github.com/dframe/cron.git
git subsplit publish --heads="master v4.1" src/Loader:https://github.com/dframe/loader.git
git subsplit publish --heads="master v4.1" src/Router:https://github.com/dframe/router.git
git subsplit publish --heads="master v4.1" src/Session:https://github.com/dframe/session.git
git subsplit publish --heads="master v4.1" src/Token:https://github.com/dframe/token.git
git subsplit publish --heads="master v4.1" src/View:https://github.com/dframe/view.git

rm -rf .subsplit/
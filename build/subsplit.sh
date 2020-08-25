git subsplit init https://github.com/dframe/dframe.git
git subsplit publish --heads="master v4.1" src/Config:https://github.com/dframe/config.git
git subsplit publish --heads="master v4.1" src/Token:https://github.com/dframe/token.git
git subsplit publish --heads="master v4.1" src/Session:https://github.com/dframe/session.git

rm -rf .subsplit/
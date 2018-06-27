pipeline {
  agent any
  stages {
    stage('Build') {
      steps {
        git(url: 'https://github.com/dframe/dframe', branch: 'master')
      }
    }
  }
  environment {
    HTTP_HOST = 'dframeframework.com'
    MOD_REWRITE = 'true'
  }
}
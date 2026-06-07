pipeline {
agent any

```
stages {

    stage('Checkout') {
        steps {
            checkout scm
        }
    }

    stage('Build Image') {
        steps {
            sh 'docker build -t lavsms:${BUILD_NUMBER} .'
        }
    }

    stage('Verify Image') {
        steps {
            sh 'docker images | grep lavsms'
        }
    }
}
```

}

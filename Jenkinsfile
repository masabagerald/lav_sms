pipeline {
    agent any

    environment {
        IMAGE_NAME = "lavsms"
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Show Workspace') {
            steps {
                sh 'pwd'
                sh 'ls -la'
            }
        }

        stage('Build Docker Image') {
            steps {
                sh 'docker build -t ${IMAGE_NAME}:${BUILD_NUMBER} .'
            }
        }

        stage('Verify Docker Image') {
            steps {
                sh 'docker images | grep ${IMAGE_NAME}'
            }
        }

    }

    post {
        success {
            echo "Build ${BUILD_NUMBER} completed successfully"
        }

        failure {
            echo "Build ${BUILD_NUMBER} failed"
        }
    }
}
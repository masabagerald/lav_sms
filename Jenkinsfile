pipeline {
    agent any

    stages {

        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Show Files') {
            steps {
                sh 'pwd'
                sh 'ls -la'
            }
        }

        stage('Docker Check') {
            steps {
                sh 'docker ps'
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

    post {
        success {
            echo 'Pipeline completed successfully!'
        }

        failure {
            echo 'Pipeline failed!'
        }
    }
}
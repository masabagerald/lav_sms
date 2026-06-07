pipeline {
    agent any

    environment {
        IMAGE_NAME = "lavsms"
    }

    stages {

       /*  stage('Checkout') {
            steps {
                checkout scm
            }
        } */

        stage('Build Docker Image') {
            steps {
                sh '''
                    docker build -t lavsms:${BUILD_NUMBER} .
                    docker tag lavsms:${BUILD_NUMBER} lavsms:latest
                '''
            }
        }

        stage('Start Environment') {
            steps {
                sh 'docker compose up -d'
                sh 'sleep 15'
            }
        }

        stage('Run Tests') {
            steps {
                sh 'docker compose exec -T app php artisan test'
            }
        }

        stage('Verify Docker Image') {
            steps {
                sh 'docker images | grep ${IMAGE_NAME}'
            }
        }
    }

    post {
        always {
            sh 'docker compose down || true'
        }
    }
}
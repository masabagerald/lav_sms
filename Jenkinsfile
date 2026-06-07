pipeline {
    agent any

    environment {
        IMAGE_NAME = "lavsms"
    }

    stages {

        stage('Build Docker Image') {
            steps {
                sh '''
                    docker build -t ${IMAGE_NAME}:${BUILD_NUMBER} .
                    docker tag ${IMAGE_NAME}:${BUILD_NUMBER} ${IMAGE_NAME}:latest
                '''
            }
        }

        stage('Debug Files') {
            steps {
                sh '''
                    pwd
                    ls -la
                    ls -la docker
                    ls -la docker/nginx
                '''
            }
        }

        stage('Inspect Nginx Config') {
            steps {
                sh '''
                    ls -l docker/nginx/default.conf
                    cat docker/nginx/default.conf
                '''
            }
        }

        stage('Start Environment') {
            steps {
                sh '''
                    docker compose down || true
                    docker compose up -d
                    sleep 15
                    docker compose ps
                '''
            }
        }

        stage('Inspect App') {
            steps {
                sh '''
                    docker compose ps

                    docker compose exec -T app pwd
                    docker compose exec -T app ls -la
                    docker compose exec -T app ls -la /var/www
                '''
            }
        }

        stage('Prepare Laravel') {
            steps {
                sh '''
                    docker compose exec -T app php artisan config:clear
                    docker compose exec -T app php artisan cache:clear
                '''
            }
        }

      /*   stage('Run Tests') {
            steps {
                sh '''
                    docker compose exec -T app php artisan test
                '''
            }
        }
 */
        stage('Verify Docker Image') {
            steps {
                sh '''
                    docker images | grep ${IMAGE_NAME}
                '''
            }
        }
    }

    post {
        always {
            sh '''
                docker compose logs app || true
                docker compose logs nginx || true
                docker compose logs postgres || true
                docker compose down || true
            '''
        }
    }
}
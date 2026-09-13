pipeline {
    agent { label 'linux-agent' }

    options {
        buildDiscarder(logRotator(numToKeepStr: '10'))
        timeout(time: 30, unit: 'MINUTES')
        disableConcurrentBuilds()
    }

    triggers {
        githubPush()
    }

    environment {
        PHP_VERSION   = '8.4'
        NODE_VERSION  = '22'
        APP_ENV       = 'testing'
        APP_KEY       = 'base64:placeholder_replaced_by_key_generate'
        PATH          = "/home/adminx/.bun/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:${env.PATH}"
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Install Dependencies') {
            parallel {
                stage('Composer') {
                    steps {
                        sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'
                    }
                }
                stage('Node') {
                    steps {
                        sh 'bun install --frozen-lockfile'
                    }
                }
            }
        }

        stage('Setup Application') {
            steps {
                sh '''
                    [ -f .env ] || cp .env.example .env
                    php artisan key:generate --force
                    php artisan migrate --force
                '''
            }
        }

        stage('Build Assets') {
            steps {
                sh 'bun run build'
            }
        }

        stage('CI Checks') {
            parallel {
                stage('Frontend Lint & Types') {
                    steps {
                        sh 'bun run types:check'
                    }
                }
                stage('PHP Lint') {
                    steps {
                        sh 'vendor/bin/pint --parallel --test'
                    }
                }
                stage('PHPStan') {
                    steps {
                        sh 'vendor/bin/phpstan analyse --no-progress'
                    }
                }
            }
        }

        stage('Tests') {
            steps {
                sh 'php artisan config:clear --ansi'
                sh 'php artisan test --compact --parallel'
            }
            post {
                always {
                    // Publish JUnit results if available
                    junit allowEmptyResults: true, testResults: 'storage/test-results/**/*.xml'
                }
            }
        }

    }

    post {
        success {
            echo "✅ Pipeline passed on branch ${env.BRANCH_NAME} (${env.GIT_COMMIT?.take(7)})"
        }
        failure {
            echo "❌ Pipeline failed on branch ${env.BRANCH_NAME} (${env.GIT_COMMIT?.take(7)})"
        }
        cleanup {
            cleanWs()
        }
    }
}

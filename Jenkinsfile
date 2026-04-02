pipeline {
    agent any
    environment {
        COMPOSER_HOME = "$HOME/.composer"
        COMPOSER_CACHE = "$HOME/.composer/cache"
        NPM_CACHE = "$HOME/.npm"
    }
    stages {
        stage('Checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/David-IT2/AfyaRescue.git'
            }
        }

        stage('Install PHP Dependencies') {
            steps {
                sh 'composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --no-progress'
            }
        }

        stage('Prepare Environment') {
            steps {
                sh 'cp .env.example .env'
                sh 'php artisan key:generate --force'
                sh 'cp .env .env.testing'
            }
        }

        stage('Install Node Dependencies') {
            steps {
                sh 'npm ci --cache $NPM_CACHE --prefer-offline'
            }
        }

        stage('Build Assets') {
            steps {
                sh 'npm run build'
            }
        }

        stage('Run Tests') {
            steps {
                sh 'php artisan test'
            }
        }

        // Deployment stage is commented out for now
        /*
        stage('Deploy (Optional)') {
            steps {
                sh 'rsync -avz --exclude=.env ./ user@yourserver:/var/www/afyarescue'
            }
        }
        */
    }
    post {
        success {
            echo 'Build completed successfully!'
        }
        failure {
            echo 'Build failed!'
        }
    }
}
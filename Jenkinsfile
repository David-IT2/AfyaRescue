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
                // Use Composer cache to speed up install
                sh '''
                composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --no-progress
                '''
            }
        }

        stage('Prepare Environment') {
            steps {
                // Copy .env.example if .env doesn't exist
                sh 'cp .env.example .env || echo ".env exists"'

                // Generate APP_KEY if missing
                sh '''
                if ! grep -q APP_KEY=. .env; then
                    php artisan key:generate --ansi
                fi
                '''

                // Copy to .env.testing for Feature tests
                sh 'cp .env .env.testing'
            }
        }

        stage('Install Node Dependencies') {
            steps {
                // Use npm cache to speed up builds
                sh '''
                npm ci --cache $NPM_CACHE --prefer-offline
                '''
            }
        }

        stage('Run Tests') {
            steps {
                sh 'php artisan test'
            }
        }

        stage('Build Assets') {
            steps {
                sh 'npm run build'
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

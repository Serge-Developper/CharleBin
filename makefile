install:
	bin/composer install

start:
	php -S localhost:8080

test:
	# cd tst && ../vendor/bin/phpunit
	./vendor/bin/phpunit tst
lint:
	# PHP Code Sniffer : vérifie le style de code (PSR-12)
	./vendor/bin/phpcs --standard=PSR12 lib/
	# PHPStan : analyse statique pour trouver les bugs
	./vendor/bin/phpstan analyse lib/ --level=1
	# PHPMD : détecte le code trop complexe
	./vendor/bin/phpmd ./lib ansi codesize,unusedcode,naming
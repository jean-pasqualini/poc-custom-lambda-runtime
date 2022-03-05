build: clean-build
	composer install
	zip -rj runtime.zip bin runtime
	zip -r extensions.zip extensions
	zip -r vendor.zip vendor/
	zip hello.zip src/lambda/hello.php

clean-build:
	rm *.zip || true

create-role:
	aws --profile "${AWS_PROFILE}" iam create-role \
	    --role-name LambdaPhpExample \
	    --path "/service-role/" \
	    --assume-role-policy-document file://./trust-policy.json

attach-policy:
	aws --profile "${AWS_PROFILE}" iam attach-role-policy --role-name LambdaPhpExample --policy-arn arn:aws:iam::aws:policy/service-role/AWSLambdaBasicExecutionRole

publish-layers:
	aws --profile "${AWS_PROFILE}" lambda publish-layer-version \
		--layer-name php-custom-runtime \
		--zip-file fileb://runtime.zip \
		--region eu-west-3

	aws --profile "${AWS_PROFILE}" lambda publish-layer-version \
		--layer-name php-custom-vendor \
		--zip-file fileb://vendor.zip \
		--region eu-west-3

	aws --profile "${AWS_PROFILE}" lambda publish-layer-version \
		--layer-name php-custom-extensions \
		--zip-file fileb://extensions.zip \
		--region eu-west-3

delete-function:
	aws --profile "${AWS_PROFILE}" lambda delete-function --function-name php-example-hello

create-function:
	aws --profile "${AWS_PROFILE}" lambda create-function \
		--function-name php-custom-lambda \
		--handler hello \
		--zip-file fileb://./hello.zip \
		--runtime provided \
		--role "arn:aws:iam::${AWS_ACCOUNT_ID}:role/service-role/LambdaPhpExample" \
		--region ${AWS_REGION} \
		--layers "arn:aws:lambda:${AWS_REGION}:${AWS_ACCOUNT_ID}:layer:php-custom-runtime:1" \
		         "arn:aws:lambda:${AWS_REGION}:${AWS_ACCOUNT_ID}:layer:php-custom-extensions:1" \
			  "arn:aws:lambda:${AWS_REGION}:${AWS_ACCOUNT_ID}:layer:php-custom-vendor:1"

invoke-function:
	aws --profile treezor-playground lambda invoke \
		--function-name php-custom-lambda \
		--region ${AWS_REGION} \
		--log-type Tail \
		--query 'LogResult' \
		--output text \
		--payload '{"command": "pwd"}' /dev/stdout | base64 --decode

hp:
	echo "${AWS_ACCOUNT_ID}"
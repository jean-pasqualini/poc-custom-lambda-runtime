#### Description
This repository contains a custom lambda runtime written in php which is able to run lambda written in php.<br>
We have also a custom extension written in PHP.

This lambda is compatible with the lambda shell lsh.

#### Your challenge
Do it in your favorite language without use the native runtime if aws has it.

#### Requirement
- An aws account
- AWS CLI installed and configured with a profile
- php installed in version 7.X
- composer installed
- zip


#### Use
1) composer install
2) export AWS_ACCOUNT_ID="your-aws-account-id"
3) export AWS_PROFILE="your-aws-cli-profile"
4) export AWS_REGION="your-aws-region"
5) make build
6) make create-role
7) make attach-policy
8) make publish-layers
9) make create-function
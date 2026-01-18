#!/bin/bash

echo "Creating SQS queue..."
awslocal sqs create-queue --queue-name price-tracker-jobs
echo "SQS queue created successfully!"

#!/bin/bash

echo "Initializing AWS services..."

# Create SQS queue
echo "Creating SQS queue..."
awslocal sqs create-queue --queue-name price-tracker-jobs
echo "SQS queue created!"

# Create S3 bucket
echo "Creating S3 bucket..."
awslocal s3 mb s3://price-tracker-images
echo "S3 bucket created!"

echo "AWS initialization complete!"

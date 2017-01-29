integration Sample Project
===============

A sample project which implements Message/Event Driven approach.
With this approach,integration services are decoupled from the main application to another integration application.
Also, the interaction between the main application and integration app is asynchronous.

# Framework and Tools
Symfony3, JWT , SNS, SQS 

# Steps
Firstly, create a Topic by AWS Console, AWS Command or AWS API.
Secondly, setup subscriber, I setup one email endpoint and one SQS.
For SQS, we need to Add Permissions to allow receive message from SNS.
see [reference](http://docs.aws.amazon.com/sns/latest/dg/SendMessageToSQS.html)
Then, we use AWS PHP SDK to publish message to SNS.

Lastly, publishing message by AWS SNS API. Then, the subscriber will receive the message from SNS Topic.

# SQS Notes
1. If we specify a number greater than 0 to `WaitTimeSeconds` when we call `receiveMessage` method.
We are actually using [AWS Long Polling](http://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-long-polling.html)
2. Even If we specify a number greater than 0 to `MaxNumberOfMessages` when we call `receiveMessage` method.
SQS may send you a number of messages which is fewer than the number you specify to `MaxNumberOfMessages`. For example, If we have 10 messages in the queue,
and we set 10 to `MaxNumberOfMessages`, we may still get only one message as SQS is distributed. However,
If we have a large number of messages (e.g. 10,000 messages) in the queue, we will have a high chance to get all of the messages up to `MaxNumberOfMessages`.
This is explained by [AWS PHP SDK Reference](http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#receivemessage) 

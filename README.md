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
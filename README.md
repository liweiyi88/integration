[![CircleCI](https://circleci.com/gh/liweiyi88/integration/tree/master.svg)](https://circleci.com/gh/liweiyi88/integration/tree/master)
[![Code Climate](https://codeclimate.com/github/liweiyi88/integration/badges/gpa.svg)](https://codeclimate.com/github/liweiyi88/integration)

Purpose
-------
This project is to demonstrate the way to integrate application with third-party services by using asynchronous messaging (Background Worker & Message Queue).

How does this demo work?
------------------------
After submitting the simple form, the application will save the form data in the database and push a message to AWS SQS. A background worker keeps running to poll the message from SQS and 
process it (pushing a new tweet to twitter in this case). This is a typical use case that "when something happens, we need to do a,b,c...". We can
process a,b,c... in either synchronous or asynchronous ways which depends on your business rules. Read [my post](https://medium.com/@weiyi.li713/integrate-web-application-with-external-systems-by-using-message-queue-ac201469c02d) for more explanations

Tools used by this project
--------------------------
`Redis` - it is used to determine whether to restart the background worker. It is inspired by [Laravel Queue](https://github.com/illuminate/queue).
`Supervisor` - it is used to auto-restart workers if they are stopped by any exception.

# Finixio - DashFx PHP API tech test

This docker environment contains a slimmed down version of the Finixio codebase that deals with API Integrations.  
- A fake broker API has been written which accepts leads and some some basic validation here: [scripts/fake-broker-api.php](/scripts/fake-broker-api.php).  
- An Integration has been written to the fake broker specs which can be found here: [DashFx](/src/Integrations/Infrastructure/DashFx.php).  
- **An incomplete Unit Test for the Integration has been written here: [DashFxTest](/tests/Integrations/Infrastructure/DashFxTest.php)**  

## Your task
- Your task is to complete the unit tests identified in [DashFxTest](/tests/Integrations/Infrastructure/DashFxTest.php).

## Local dev instructions
- The entire infrastructure is dockerised, so you will need [docker](https://www.docker.com/) installed.
- We use a [Makefile](/Makefile) to wrap docker into easy to run commands.  
- All operations can be run within docker containers. You are not expected or required to have PHP installed on your host machine.  

### Steps
1. Please run `make tests`, this will install the composer packages and run the phpunit test framework.  
2. You will notice that there are 2 passing tests and others that are incomplete.
3. Each time you make changes to [DashFxTest](/tests/Integrations/Infrastructure/DashFxTest.php) you can run `make tests` to trigger phpunit again.
4. Test is complete when all tests are passing.
5. If you want to see the [DashFx](/src/Integrations/Infrastructure/DashFx.php) object in action use `make run` to execute [scripts/run.php](/scripts/run.php) against the fake broker api.


## Helpful commands
You can spin up the fake broker api by running:  
```shell
make broker-up
```
This will set the broker API running on [http://localhost:10101](http://localhost:10101).  
You can now run `make run` which will invoke [scripts/run.php](/scripts/run.php) and send lead data to it.  
Alternatively you can also use `curl`: 
```shell
# valid request
curl --location --request POST 'http://localhost:10101' \
    --header 'Authorization: s3cr3tk3y' \
    --header 'Content-Type: application/json' \
    --data-raw '{
        "firstName" : "test",
        "lastName" : "test",
        "email" : "test@test.com",
        "phone": "+493022610",
        "country": "GB",
        "ip": "93.241.38.109"}'
        
       
```
_pro tip: this can be imported into postman_


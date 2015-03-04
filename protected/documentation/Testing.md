# Testing

StreamBed does not lend itself to a conventional unit test development process.

Firstly it has a large number of data transportation methods; functions that do little more than transport large blobs of data from one domain to another. The functions would could either have very simple tests that simply test that they are passing some data, but would not be robust enough for test driven development. Or they could be more complex tests that would take much longer than the function to develop and lead to a lot of code duplication as similar functions are used on different domains.

Secondly, StreamBed has a requirement to make extensive use of live testing, to ensure that data passed between domains is as expected (Different domains can be run by different companies/services etc).

A strategy has been developed in order to get around this complexity and still allow for test driven development, albeit in an unconventional manner.

Firstly, all data that is passed between domains has to conform to the Babbling Brook protocol.

### JavaScript models

The different domains communicate via JavaScript IFRAME postMessage commands. These are picked up by that domains Controller.js module and the data is tested against a model in /js/Shared/Models.js

If the data fails to pass the test then an error is raised.  When developing new features, the first code written should be these models. They make it possible to develop the rest of the code (except the client UX) in a test driven manner by simply making a client side call to the domus domain.

In addition to the JavaScript models, there are server side forms and database models that validate any submitted data. These are written next as they provide meaningful error messages.

### Form models

On all domains, when data is submitted it is validated using form classes found in /protected/models/forms.

### Database models

When submitting data to the database it should always be done via the /protected/models class that is associated with the relevant table. If inserting to multiple tables then use a class in /protected/models/multi that in turn uses the model classes, usually within a transaction.

### Client UX

There is also a full set of SeleniumIDE acceptance tests in /protected/tests/selenium tests/main test suit. However they are broken and out of date. They are in the process of being refactored into Codecept acceptance tests in /protected/tests/codecept/tests/acceptance.

### Unit testing

There are plans to reintroduce unit testing to a few specific classes that could benefit from it. Namely the /js/Domus/Filter.js, /js/Domus/ManageTakes.js and /js/Domus/FetchPosts.js which are all complex enough to justify another layer of testing.

### Refactoring

The main problem with this methodology is that it is hard to refactor across the codebase as there is no test suite. The plan is to create a set of protocol tests that can be applied to any Babbling Brook domain. This will not only make refactoring easier, but will also double as a method to test that other datastores in the network are behaving properly.

# Pingpong
## An app to reserve a single ping pong table via Slack commands
_*In response to Scott Gifford's poor performance on a white board interview with Jon Frankel, Benjamin Xia-Reinert._

### Things to note:
* I built this over a weekend from memory, so I'm sure I got a business rule or two wrong. 
* I haven't deployed it, so it isn't actually integrated with Slack yet.
* I've only interacted with the code through phpunit.

### How to run locally
1. Clone repository
2. Run `cd pingpong`, `cp .env.example .env`, `composer install`, `php artisan key:generate`
3. Finally run `phpunit`

## Definitions
* **User**: An entity related to the Slack channel's user, via their unique username.
* **Game**: An entity that consists of a start timestamp and an end timestamp. 
Relates to a User via a pivot table (many:many), the number of users per game is restricted in the Game model. 
The reason it's restricted in the code (and not the database) is so, in the future, you can add things like 2v2 games.
    * **Open Game (scope)**: A game that has not started yet `started_at is null` and has one user attached.
    * **Game in Progress (scope)**: A game that has two users, has started `started_at is not null`, and has not ended yet `ended_at is null`.

_*Game scopes will have to be redefined once you expand the users per game passed two. 
There will likely need to be more explicit booleans and functionality to accomplish that feature._

## How to use (if it were integrated with Slack)
There are two ways to interact with the app. One way is to initiate a game `/looking` and the second is to `/join` an existing/open game.

### /looking
There are a few things that can happen when you send the `/looing` command:
* If your user doesn't exist, it will create it using your Slack username.
* If there is already an open game, you will join that one.
* If there are no open games it will initiate your game.

### /join
* If there is an open game, you join it.
* If there are no open games, you get this message "No available games." and you'll have to wait.
* Once you join an open game a 30 minute countdown will start, when that countdown reaches 0 the game will end.

## Conclusion
I felt I wasn't able to represent myself very well in the online whiteboard interview.
I hope this shows that I have a strong grasp on data modeling, building APIs, OOP, and PHP/Laravel.
That being said, even this project is limited and is intended to show competence, not a full functioning production ready app.
There are decision made here to simply show knowledge/execution of features and patterns that I may have reconsidered in a production app.

# PHP Hackathon
This document has the purpose of summarizing the main functionalities your application managed to achieve from a technical perspective. Feel free to extend this template to meet your needs and also choose any approach you want for documenting your solution.

## Problem statement
*Congratulations, you have been chosen to handle the new client that has just signed up with us.  You are part of the software engineering team that has to build a solution for the new client’s business.
Now let’s see what this business is about: the client’s idea is to build a health center platform (the building is already there) that allows the booking of sport programmes (pilates, kangoo jumps), from here referred to simply as programmes. The main difference from her competitors is that she wants to make them accessible through other applications that already have a user base, such as maybe Facebook, Strava, Suunto or any custom application that wants to encourage their users to practice sport. This means they need to be able to integrate our client’s product into their own.
The team has decided that the best solution would be a REST API that could be integrated by those other platforms and that the application does not need a dedicated frontend (no html, css, yeeey!). After an initial discussion with the client, you know that the main responsibility of the API is to allow users to register to an existing programme and allow admins to create and delete programmes.
When creating programmes, admins need to provide a time interval (starting date and time and ending date and time), a maximum number of allowed participants (users that have registered to the programme) and a room in which the programme will take place.
Programmes need to be assigned a room within the health center. Each room can facilitate one or more programme types. The list of rooms and programme types can be fixed, with no possibility to add rooms or new types in the system. The api does not need to support CRUD operations on them.
All the programmes in the health center need to fully fit inside the daily schedule. This means that the same room cannot be used at the same time for separate programmes (a.k.a two programmes cannot use the same room at the same time). Also the same user cannot register to more than one programme in the same time interval (if kangoo jumps takes place from 10 to 12, she cannot participate in pilates from 11 to 13) even if the programmes are in different rooms. You also need to make sure that a user does not register to programmes that exceed the number of allowed maximum users.
Authentication is not an issue. It’s not required for users, as they can be registered into the system only with the (valid!) CNP. A list of admins can be hardcoded in the system and each can have a random string token that they would need to send as a request header in order for the application to know that specific request was made by an admin and the api was not abused by a bad actor. (for the purpose of this exercise, we won’t focus on security, but be aware this is a bad solution, do not try in production!)
You have estimated it takes 4 weeks to build this solution. You have 2 days. Good luck!*

## Technical documentation
### Data and Domain model
User:
    Users can be created using the registerUser endpoint. User can be admin (role 1) or client (role 2).
    User can only be created with a valid CNP and Name.
    Admins can create new programs using the addProgram endpoint.
    Client can book a program using the addBooking endpoint.
    
Programs:
    Programs can be created and deleted only by admins. Each program must have a type and a room.  
    Rooms are hardcoded in the database and contain id and name.
    Program types are hardcoded in the database and contain id and name.
    Programs must have an start_date and end_date and can be limited to a number of maximum participants.
    Users can view the programs using the getPrograms.php
    
Booking:
    Client can register to a program using the addBooking.php endpoint.
    In order to register, client must have a valid account and provide the program id.
    A number of validations will take place when booking.
    If successfully validated the user will be booked to the program.

### Application architecture
Application contains a main class "Hackathon" that is used to connect to the database and allow registration of users.
Class "Admin" extends the main class and allows admin users to create and delete programs.
Class "Client" extends the main class and allows client to register to a program.
Endpoints are created in the /api folder and each will call the specific function from the object.
Database contains the following tables:
    - user: id, role, cnp, name
    - programs: id, type_id, room_id, max_user, start_date, end_date
    - program_type: id, name
    - rooms: id, name
    - booking: id, user_id, program_id

###  Implementation
##### Functionalities
[x] Register user
        Endpoint: /api/registerUser.php
        Description: Adds a new user to database via a valid CNP. User must have a role - 1 (Admin) or 2 (Client) and a name.
        Request Type: POST
        Request Parameters (JSON):
        {
            "cnp": (int),
            "role": (int),
            "name": (string)
        }
[x] Create programme
        Endpoint: /api/addProgram.php
        Description: Adds a new program to database via a valid CNP of a Admin. 
        Request Type: POST
        Request Parameters (JSON):
        {
            "cnp": (int) ,
            "type": (int)
            "room": (int)
            "start_date": (timestamp),
            "end_date": (timestamp),
            "max_user": (int)
        }
[x] Delete programme
        Endpoint: /api/deleteProgram.php
        Description: Deletes a program from the database based on a valid CNP of Admin. 
        Request Type: GET
        Request Parameters (JSON):
        {
              "cnp": (int),
              "program": (int)
        }
[x] Book a programme
        Endpoint: /api/addBooking.php
        Description: Adds a new booking to database using a valid client CNP.
        Request Type: GET
        Request Parameters (JSON):
        {
              "cnp": (int),
              "program": (int)
        }
[x] Get programs
        Endpoint: /api/getPrograms.php
        Description: Gets all programs from the database.
        Request Type: GET
        Response exemple (JSON):
        [
            {
                "id": "14",
                "type_id": "2",
                "room_id": "3",
                "max_user": "2",
                "start_date": "2021-04-17 21:27:52",
                "end_date": "2021-04-17 21:27:47"
            },
            {
                "id": "16",
                "type_id": "2",
                "room_id": "2",
                "max_user": "10",
                "start_date": "2021-04-30 10:44:43",
                "end_date": "2021-04-30 10:44:35"
            }
        ]
##### Business rules
In order to use the application, a valid user must be created. In order to register an accont, a valid CNP and Name is needed.
Validations take part on both admin and client side:
    Admin:
        - when creating a program we validate account and that provided program_type and room exists in the database. Also program must have start_date and end_date (end_date cannot be lower that start_date) and max_users must be provided
        - when deleting a program we validate account and that program_id exists in the database
    Client:
        - validate that user has an account
        - validate if user role is Client
        - validate if the program exists and program date is not due
        - validate if the client is not already booked to the program
        - validate if client does not have another booked program in the same time interval
        - validate if program maximum participants has not been reached 

##### Environment
| Name | Choice |
| Operating system (OS) | Windows 10 |
| Database  | MySQL 8.0|
| Web server| Apache |
| PHP | 7.3 |
| IDE | Brackets |

### Testing
Each endpoint has been tested using POSTMAN.

## Feedback
In this section, please let us know what is your opinion about this experience and how we can improve it:

1. Have you ever been involved in a similar experience? If so, how was this one different?
    No, this is the first experience of this kind.
    
2. Do you think this type of selection process is suitable for you?
    Yes, it was a good experience for me because it made me learn more.It challenged me to give my best to deliver my solution for this task.
    
3. What's your opinion about the complexity of the requirements?
    This test was pretty difficult in my opinion, but it made me really focus on the task and search different approaches and solutions.
    
4. What did you enjoy the most?
    I liked the free choice of how to implement this application.
    
5. What was the most challenging part of this anti hackathon?
    The most challenging part of this was , in my opinion, the validation of the programs to not overlap. And also I didn't have much experience with API and it gave me more work.
    
6. Do you think the time limit was suitable for the requirements?
    Yes. Sometimes less is more.
    
7. Did you find the resources you were sent on your email useful?
    Yes, it helped me be prepared better.
    
8. Is there anything you would like to improve to your current implementation?
    With more time I would optimize my work better and have more validations and also security.
    
9. What would you change regarding this anti hackathon?
    In this moment nothing.


# plusbot - A Slack Addon application
### Application intending to rebuild the functionality of the original plusplus application. 

- Not intended for any sort of distribution. Ie intended for use by only my own team
- Database queries need some work (converted from text file score storage)

- Version 1.0:
  - How does it work?
    - Listens to group chat messages using Slack event notification service
    - parses and greps through received messages for @<username> ++ or @<username> --
    - finds and updates the current score in the mysql database
    - uses php curl to post back a message to the chatroom where the message was left with encouraging or discouraging remark
    - Validates that user cannot modify their own score and proper repudiation message sent back. 
  - Things feature not implemented, the implementation is started in this version, but the actual implementation is present in the earlier version which may have been lost ... 

# MSM Member Group Switcher for ExpressionEngine 2

Switch member's group depending on what MSM site is he at.

This module and extension is intended for multi-site ExpressionEngine installations that use Multiple Site Manager. It allows to switch member’s group depending on what MSM site is he at, in order to have different permissions set for member on each site.

For example, the user can be admin on site A, regular member on site B and content editor on site C; or regular member on sites A and B and content moderator on site C. With regular EE setup, you would need to create many member groups with complex permissions, sometime with one member per group - which can become very complex when you have many groups or many sites. With this add-on, you need to create only several “basic” groups and if user needs different permissions on some site - simply assign him to a different membership group for that site.

The group that user is registered with (or assigned by admin by editing his EE profile) is his “master” group. It is used for all MSM sites, unless there is special group assigned to him in MSM Member Group Switcher module Control Panel.

If there is a record for the user in MSM Member Group Switcher then special group set there is used. The switch is done globally, both for front-end and Control Panel. When user navigate to different site - the group switch is done again.

To assign member to a different membership group for certain MSM site, navigate to Add-Ons -> Modules -> MSM Member Group Switcher in EE Control Panel. Then, find the member you need (you can use search field to filter by username, screen name or email) and click his username to go to editing page. You then are able to assign membership group for each MSM site that you have to that member. Note: if the editing is done not by Super Admin, only groups for the sites that currently editing admin user has access to can be assigned.

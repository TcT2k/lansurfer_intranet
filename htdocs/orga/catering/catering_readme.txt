Catering Script by Henrik Brinkmann aka Hotkey  CNC e.V.
	Team CNC e.V. (http://cnc.lanparty.de)
	Team UNION    (http://union.lanparty.de)
	
	Contact : hotkey@cncev.de
	
	Any comment or help is apreciated !


First of all : It is only allowed to use this Script together with the lansurfer 
(lanparty management) script.
Information about LANsurfer is available at http://www.lansurfer.com .

....:::: !!! IMPORTANT NOTE !!! ::::....

I CAN NOT GARANTADE THAT THIS SCRIPT IS BUGFREE. AND SINCE IT MANAGES THE CATERING
ON A LANPARTY IT GOES TOGETHER WITH MONEY. SO BE AWARE OF THAT !

....:::: !!! IMPORTANT NOTE !!! ::::....


A few words about the script:
=============================
The Script is completely written with PHP (and HTML of course) ;-)
The Data is handled with a MySQL Database.
For Developement i've used XITAMI Webserver, MySQL shareware and UltraEdit.

What for ?
==========
Like i've mentioned above this Script manages the Catering System on LANparties.
The User can buy some virtual Funds and is than able to order his Pizza, Coke, Beer 
or anything else via Intranet.

The Orga-user is than able to handle all orders comfortable. More Info hat http://www.lansurfer.com


The Database:
=============

CatProduct :
+--------------+--------------+------+-----+---------+----------------+
| Field        | Type         | Null | Key | Default | Extra          |
+--------------+--------------+------+-----+---------+----------------+
| id           | int(11)      |      | PRI | NULL    | auto_increment |
| name         | varchar(255) |      |     |         |                |
| beschreibung | text         |      |     |         |                |
| preis        | double(16,2) |      |     | 0.0000  |                |
| vorhanden    | int(11)      |      |     | 0       |                |
| lieferant    | int(11)      |      |     | 0       |                |
| size         | int(11)      |      |     | 0       |                |
| nummer       | int(11)      |      |     | 0       |                |
+--------------+--------------+------+-----+---------+----------------+


CatOrder :
+--------------+---------+------+-----+---------+----------------+
| Field        | Type    | Null | Key | Default | Extra          |
+--------------+---------+------+-----+---------+----------------+
| id           | int(11) |      | PRI | 0       | auto_increment |
| user_id      | int(11) | YES  |     | NULL    |                |
| angebot_id   | int(11) | YES  |     | NULL    |                |
| eingetroffen | int(11) | YES  |     | NULL    |                |
| ausgeliefert | int(11) | YES  |     | NULL    |                |
| anzahl       | int(11) | YES  |     | NULL    |                |
| bearbeitet   | int(11) | YES  |     | NULL    |                |
| hour         | int(11) | YES  |     | NULL    |                |
| minute       | int(11) | YES  |     | NULL    |                |
| date         | date    | YES  |     | NULL    |                |
| wagen        | int(11) | YES  |     | NULL    |                |
+--------------+---------+------+-----+---------+----------------+



CatSupplier :
+---------+--------------+------+-----+---------+----------------+
| Field   | Type         | Null | Key | Default | Extra          |
+---------+--------------+------+-----+---------+----------------+
| id      | int(11)      |      | PRI | NULL    | auto_increment |
| name    | varchar(255) |      |     |         |                |
| telefon | text         | YES  |     | NULL    |                |
| knr     | text         | YES  |     | NULL    |                |
+---------+--------------+------+-----+---------+----------------+



CatHistory :
+----------+---------+------+-----+---------+----------------+
| Field    | Type    | Null | Key | Default | Extra          |
+----------+---------+------+-----+---------+----------------+
| id       | int(11) |      | PRI | NULL    | auto_increment |
| zeit     | text    | YES  |     | NULL    |                |
| group_id | int(11) | YES  |     | NULL    |                |
+----------+---------+------+-----+---------+----------------+



CatHistoryItems :
+---------------+---------+------+-----+---------+----------------+
| Field         | Type    | Null | Key | Default | Extra          |
+---------------+---------+------+-----+---------+----------------+
| id            | int(11) |      | PRI | NULL    | auto_increment |
| bestellung_id | int(11) | YES  |     | NULL    |                |
| group_id      | int(11) | YES  |     | NULL    |                |
| name          | text    | YES  |     | NULL    |                |
| size          | text    | YES  |     | NULL    |                |
| anzahl        | int(11) | YES  |     | NULL    |                |
+---------------+---------+------+-----+---------+----------------+



CatStats:
+------------+---------+------+-----+---------+----------------+
| Field      | Type    | Null | Key | Default | Extra          |
+------------+---------+------+-----+---------+----------------+
| id         | int(11) |      | PRI | NULL    | auto_increment |
| angebot_id | int(11) | YES  |     | NULL    |                |
| anzahl     | int(11) | YES  |     | NULL    |                |
+------------+---------+------+-----+---------+----------------+


important in table user :

kontostand (double 16,2)






RELEASE TRACKING :

30.03.2001 First beta Release. 0.50
===================================

12.04.2001 Beta Ver. 0.60
=========================
Fixed Bugs :
- ordering a Product which is allready in the "warenkorb" whill now cause the correct
	addition of this order.
- If an Order is deleted by the User or the Orga the money is now traced back to 
	the users konto. Guess that would have caused some nice trouble ;-)
- "Zwischensumme" in "Einkaufswagen" is now calculated correctly (problem with the decimals)
- Minutes between 1-9 are now displayed as 01-09 (it's just nicer;-)  )
- The colors are now displayed correctly at "my konto" 
- Status of the last ordering is know easier to understand :
	the 4 values are :
		"keine Bestellung"
		"nicht bearbeitet"
		"bearbeitet"
		"abholen"
		
New Features :
- You can add a Telephone Number and a "kunden No." to the "lieferanten" (Deliver Services)
	which are additional displayed when ordering a product.
	

14.04.2001 Beta Ver. 0.70
=========================
Fixed Bugs :
- killed some small bugs

New Features :
- A Statistic Funktion which tells u all about the Products which have been sold Intern and Extern
	Additional to this you can save transactions which have been made without the LANsurfer Catering
	System (the intern Products). So these ones are saved aswell in the statistic.
	

21.04.2001 Beta Ver. 0.75
=========================
Fixed Bugs :
- Fixed a bug which converted 1000 Bucks 1 Buck when a user has klicked on the "zur Kasse" button.
- When a Shoppingcar is empty and a user klicks on the "zur Kasse" link, there is now a Message that
	there isn't something to buy.


New Features / Changes :
- Added a global Value LS_CATERING_CURRENCY which contains the actual currency for your Party.
- Changed the DB Values for "preis" in CatProduct and "kontostand" in user from DOUBLE (16,4) to (16,2).



22.04.2001 Beta Ver. 0.80
=========================
Fixed Bugs :
- none -

New Features / Changes :
- Changing the users amount of money : The amount of money you are entering in the formular is now 
	added to the money which the user allready has on his konto. 
- Changings in some Database related stuff regarding the History Functions.

30.05.2001 Beta Ver. 0.90
=========================
Fixed Bugs :
- Search Function below "neue Bestellungen" works now correct
- Many other small bugs
- removed some Debug output ;-)

New Features / Changes :

- When performing a direct transaction on a user (possible below "user konten" after specifiying 
	with the search function) it is now possible to give as well the amount of the product which 
	will be sold.
- When changing an order status the filter will be remembered. ("Neue Bestellungen")
- Import Function. It is now possible to import the Product Data with a CSV file. (Orga Main Menu)





Thanks 2 all guys in the LANsurfer Orga Board who helped me with this one !


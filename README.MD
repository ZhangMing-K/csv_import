##### Database url in the .env file
DATABASE_URL="mysql://root:123456@127.0.0.1:3307/Linkody?charset=utf8mb4"

##### Sample csv files to use
There are 2 sample csv files to use under public directory, named urls.csv and urls next.csv

##### 3 Constraints

## 1. The table can grow to millions of URLs. However, we can’t have the user wait more than a couple of seconds for inserting CSV files with tens of thousands of URLs. What are the usual approaches?
I think we can use bulk insert option here, or we can use background service to handle insert so in front end we do not wait. 
I have used bulk insert option in my solution.


## 2. The URLs can be up to 2048 characters. What problem are we facing? How to solve that problem?
First of all, when we create mysql table if we try to make the column unique with VARCHAR(2048) it throws us an error that the unique key is too big considering the max prefix length limit.
If we put it smaller something like VARCHAR(255), the string will be truncated so not all of the text will be saved into databse.
There are sveral possibilities, the one I've used in this exercise is to set the prefix length limit in mysql table. For me I've put the column to be VARCHAR(2048) and then make it unique, and put the prefix length limit to 512 (512 is compatible with 3072 bytes of limit)

## 3. We want all versions of the same URL to match. For instance, if 2 URLs differ only by the scheme, they are considered the same URL. If one URL has the default port 80 and another has no port, they are considered the same URL. If the URLs have the same query parameters and values but in different orders, they are considered the same URL.
This is handled in the front end side, there's a function called `getUniqueUrls` in the CSVController.php file.

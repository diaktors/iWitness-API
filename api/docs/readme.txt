installed : php55-intl

RFC 2616, PUT should be used only for complete replacement of a representation, in an idempotent operation. PATCH should be used for idempotent partial updates.



/resource/id  GET  -> resource::fetch($id)
/resource/id  PUT  -> resource::update  -replace existing document
/resource/id  PATCH  -> resource::patch ->partial update  //should use $this->getInputFilteredValues($inputFilter, $data)
/resource/id  DELETE  -> resource::delete



/resource     GET     ->resource::fetchAll
/resource     POST     ->resource::create //should use $inputFilter->getValues()


//normal not support
/resource     PUT     ->resource::replaceList
/resource     DELETE     ->resource::deleteList

order by example:
    //this is old and do not support any more
    1.http://0.0.0.0:8888/prospect?page=4 &size = 10 &orderBy[name]=ASC&orderBy[platform]=DESC&include[contact]&fileds[emal,fsdfs]

    //new api
    2. http://api.public.dev-vn.webonyx.local/user?phone=12015550125&sort=+firstName&page=4 &size = 10


1. How to get a Token
post message bellow to server:
http://0.0.0.0:8889/oauth

{
  "grant_type":"password",
  "client_id": "47b34ee7-f5a1-11e3-bc94-000c29c9a052",
  "username":"19545551259",
  "password":"hung1"
}

Return token:

{
    "access_token": "faf71882fc110fce1fe909b119f5ce0bca46e11f",
    "expires_in": 3600,
    "token_type": "Bearer",
    "scope": null,
    "refresh_token": "7485c81c9e77f4e4c38afd9978869a1b1d51e3db"
}

2. how to covert, concate media file: ffmpeg http://www.ffmpeg.org/
3. Extract/Write media infomation: exiftool tools http://www.sno.phy.queensu.ca/~phil/exiftool/
4. Setup deamon process
    can use:
               1. deamon tools: http://cr.yp.to/daemontools.html
               2. upstart http://upstart.ubuntu.com/
               3. Supervisor: http://supervisord.org/installing.html
               4. linux nohup nice -n -5 ls / > out.txt &


               sudo apt-get install libimage-exiftool-perl


//client id:
client_id = 47b34ee7-f5a1-11e3-bc94-000c29c9a052: iphone,  client_secret =  a214462f-23f0-11e4-b8aa-000c29c9a052 //not use right now
client_id = ba5659b4-f5a1-11e3-bc94-000c29c9a052: android  client_secret = a81af5fc-23f0-11e4-b8aa-000c29c9a052
client_id = e114cbaa-f5a1-11e3-bc94-000c29c9a052: web      client_secret =  ad990419-23f0-11e4-b8aa-000c29c9a052

new iOS key
1ef28784-23f8-11e4-b8aa-000c29c9a052 secret = 26b30aba-23f8-11e4-b8aa-000c29c9a052

* Premailer:
 - sudo gem install premailer
 - sudo gem install getopt

Migration: Prospect, Sender, Receipient: should run in order: 1. prospect.sql -> 2. sender.sq; -> 3. receipient.sql

from os import system
import requests
import json
import time
import re
from PIL import Image, ImageDraw
import numpy as np
import base64
from datetime import datetime, timezone, timedelta
import sqlite3
import shutil






maxPages = 2000

PVP_Limit = False;
PVE_Limit = False;

PVP_lastID = ''
PVE_lastID = ''


__dcfduid = ''
__sdcfduid = ''
__cfruid = ''
_cfuvid = ''
_gcl_au = ''
_ga = ''
cf_clearance = ''
OptanonConsent ='"'
_ga_Q149DFWHT7 = ''
authorization = ''
GUILD_ID =''
PVP_CHANNAL_ID = ''
PVE_CHANNAL_ID = ''
x_super_properties = ''

PVP_params = {'limit': '50'}
PVP_cookies = {
    '__dcfduid': __dcfduid,
    '__sdcfduid': __sdcfduid,
    '__cfruid': __cfruid,
    '_cfuvid': _cfuvid,
    'locale': 'en-US',
    '_gcl_au': _gcl_au,
    '_ga': _ga,
    'cf_clearance': cf_clearance,
    'OptanonConsent': OptanonConsent,
    '_ga_Q149DFWHT7': _ga_Q149DFWHT7,
}


PVP_headers = {
    'authority': 'discord.com',
    'accept': '*/*',
    'accept-language': 'en-US,en;q=0.9',
    'authorization': authorization,
    'dnt': '1',
    'referer': f'https://discord.com/channels/{GUILD_ID}/{PVP_CHANNAL_ID}',
    'sec-ch-ua': '"Chromium";v="116", "Not)A;Brand";v="24", "Google Chrome";v="116"',
    'sec-ch-ua-mobile': '?0',
    'sec-ch-ua-platform': '"Windows"',
    'sec-fetch-dest': 'empty',
    'sec-fetch-mode': 'cors',
    'sec-fetch-site': 'same-origin',
    'user-agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36',
    'x-debug-options': 'bugReporterEnabled',
    'x-discord-locale': 'en-US',
    'x-discord-timezone': 'America/New_York',
    'x-super-properties':x_super_properties ,
}
PVE_params = {'limit': '50'}
PVE_cookies = {
    '__dcfduid': __dcfduid,
    '__sdcfduid': __sdcfduid,
    '__cfruid': __cfruid,
    '_cfuvid': _cfuvid,
    'locale': 'en-US',
    '_gcl_au': _gcl_au,
    '_ga': _ga,
    'cf_clearance': cf_clearance,
    'OptanonConsent': OptanonConsent,
    '_ga_Q149DFWHT7': _ga_Q149DFWHT7,
}


PVE_headers = {
    'authority': 'discord.com',
    'accept': '*/*',
    'accept-language': 'en-US,en;q=0.9',
    'authorization': authorization,
    'dnt': '1',
    'referer': f'https://discord.com/channels/{GUILD_ID}/{PVE_CHANNAL_ID}',
    'sec-ch-ua': '"Chromium";v="116", "Not)A;Brand";v="24", "Google Chrome";v="116"',
    'sec-ch-ua-mobile': '?0',
    'sec-ch-ua-platform': '"Windows"',
    'sec-fetch-dest': 'empty',
    'sec-fetch-mode': 'cors',
    'sec-fetch-site': 'same-origin',
    'user-agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36',
    'x-debug-options': 'bugReporterEnabled',
    'x-discord-locale': 'en-US',
    'x-discord-timezone': 'America/New_York',
    'x-super-properties':x_super_properties ,
}


def CopyDB():
    source_file = 'PVP_PVE.db'
    destination_folder = '\\\\BLUESRV\\appdata\\nginx\\www\\dayz'
    try:
        shutil.copy2(source_file, destination_folder)
        print(f"File '{source_file}' copyed successfully to '{destination_folder}'.")
    except Exception as e:
        print(f"Error moving the file: {e}")


def is_Record_in_db(cursor, Victim, timestamp, InGameTime, Link, table_name):
    # Execute a SQL query to check if a record with the same combination of fields exists in the specified table
    cursor.execute(f"SELECT COUNT(*) FROM {table_name} WHERE Victim = ? AND Timestamp = ? AND InGameTime = ? AND Link = ?", (Victim, timestamp, InGameTime, Link))
    count = cursor.fetchone()[0]  # Get the count of records with the given combination of fields
    return count > 0


def processPVPDesc(description,title,timestamp,fields):
    global PVP_Limit
    data = []
    #print(description)
    Killer = ''
    Victim = ''
    Weapon = ''
    WeaponType = ''
    Distance = ''
    HitLocation = ''
    HitDamage = ''
    POS_X = ''
    POS_Y = ''
    POS_Z = ''
    Link= ''
    Item = ''
    hours = ''
    minutes = ''
    seconds = ''

    DescArray = description.splitlines()
    
    if(len(DescArray)==3):      #Grenade/Land Mine/*Unknown/Dead Entity/M79/40mm Explosive Grenade
        Stepped_On_Text = DescArray[0]	
        Stepped_On_Pattern = r'\*\*(.*?)\*\* stepped on a (.*)$'     #Stepped_On
        Stepped_On_Match = re.search(Stepped_On_Pattern , Stepped_On_Text)

        Martyrdomed_Text = DescArray[0]	
        Martyrdomed_Pattern = r'\*\*(.*?)\*\* got martyrdomed by a (.*)$'     #martyrdomed
        Martyrdomed_Match = re.search(Martyrdomed_Pattern , Martyrdomed_Text)

        Hot_Potato_Text = DescArray[0]	
        Hot_Potato_Pattern = r'\*\*(.*?)\*\* played hot potato with (.*)$'     #Hot_Potato
        Hot_Potato_Match = re.search(Hot_Potato_Pattern , Hot_Potato_Text)

        Blown_Up_Text = DescArray[0]	
        Blown_Up_Pattern = r'\*\*(.*?)\*\* was blown up by (.*)$'     #Blown_Up
        Blown_Up_Match = re.search(Blown_Up_Pattern , Blown_Up_Text)

        Location_Text = DescArray[1]	
        Location_Pattern = r'\*\*Location\*\* \[(.*?);(.*?);(.*?)\]\((.*?)\)'
        Location_Match = re.search(Location_Pattern , Location_Text)
        
        Time_Alive_Text = DescArray[2]
        Time_Alive_pattern = r"\*\*Time Alive\*\*\s(\d{2})\*\*H\*\* (\d{2})\*\*M\*\* (\d{2})\*\*S\*\*"
        Time_Alive_match = re.search(Time_Alive_pattern, Time_Alive_Text)

        if Time_Alive_match:
            hours = Time_Alive_match.group(1)
            minutes = Time_Alive_match.group(2)
            seconds = Time_Alive_match.group(3)
           
        if Stepped_On_Match:
            Victim = Stepped_On_Match.group(1)
            Item = Stepped_On_Match.group(2)   
        elif Martyrdomed_Match:
            Victim = Martyrdomed_Match.group(1)
            Item = Martyrdomed_Match.group(2)
        elif Hot_Potato_Match:
            Victim = Hot_Potato_Match.group(1)
            Item = Hot_Potato_Match.group(2)
        elif Blown_Up_Match:
            Victim = Blown_Up_Match.group(1)
            Item = Blown_Up_Match.group(2)
        if Location_Match:
            POS_X = Location_Match.group(1)
            POS_Y = Location_Match.group(2)
            POS_Z = Location_Match.group(3)
            Link = Location_Match.group(4)
  
    if(len(DescArray)==5):      #location? 
        Names_Text = DescArray[0]	
        Names_Pattern = r'\*\*(.*?)\*\* (.*?) \*\*(.*?)\*\*'
        Name_Match = re.search(Names_Pattern , Names_Text)

        Weapon_Text = DescArray[1]	
        Weapon_Pattern = r'\*\*Weapon\*\* (.*?) \((.*?)\)'
        Weapon_Match = re.search(Weapon_Pattern , Weapon_Text)

        Distance_Text = DescArray[2]	
        Distance_Pattern = r'\*\*Distance\*\* (.*?\s\w+)'
        Distance_Match = re.search(Distance_Pattern , Distance_Text)

        Hit_Text = DescArray[3]	
        Hit_Pattern = r'\*\*Hit\*\*\s+(.*?)(\s+(.*?).*?\s\w+\s)'
        Hit_Match = re.search(Hit_Pattern , Hit_Text)

        Location_Text = DescArray[4]	
        Location_Pattern = r'\*\*Location\*\* \[(.*?);(.*?);(.*?)\]\((.*?)\)'
        Location_Match = re.search(Location_Pattern , Location_Text)
     
        Time_Alive_Text = fields[1]['value']
        Time_Alive_pattern = r"\*\*Time Alive\*\*\s(\d{2})\*\*H\*\* (\d{2})\*\*M\*\* (\d{2})\*\*S\*\*"
        Time_Alive_match = re.search(Time_Alive_pattern, Time_Alive_Text)
       
        if Time_Alive_match:
            hours = Time_Alive_match.group(1)
            minutes = Time_Alive_match.group(2)
            seconds = Time_Alive_match.group(3)
           
        if Name_Match:
            Killer = Name_Match.group(1)
            Victim = Name_Match.group(3)
           
        if Weapon_Match:
            Weapon = Weapon_Match.group(1)
            WeaponType = Weapon_Match.group(2)

        if Distance_Match:
            Distance = Distance_Match.group(1)

        if Hit_Match:
            HitLocation = Hit_Match.group(1)
            HitDamage = Hit_Match.group(2)

        if Location_Match:
            POS_X = Location_Match.group(1)
            POS_Y = Location_Match.group(2)
            POS_Z = Location_Match.group(3)
            Link = Location_Match.group(4)

    InGameTime = title.replace("PVP Event -", "").strip()
    
    if (Victim != "" and Item != "" and POS_X != "" and POS_Y != "" and POS_Z != "" and Link != ""):
        print(f"Killer: {Item}, Victim: {Victim}, coords: {POS_X}/{POS_Y}")
        
        #print(f"Killer: {Item}, Victim: {Victim}, coords: {POS_X}/{POS_Y} Time Alive: Hours: {hours}, Minutes: {minutes}, Seconds: {seconds} | TimeStamp: {timestamp} | InGameTime: {InGameTime}")
        pvp_data = (
            Item,
            Victim,
            POS_X,
            POS_Y,
            POS_Z,
            Link,
            hours,
            minutes,
            seconds,
            timestamp,
            InGameTime
        )
        if not is_Record_in_db(cursor, Victim, timestamp,InGameTime,Link, "PVP"):
            cursor.execute('''
            INSERT INTO PVP (
                Killer,
                Victim,
                POS_X,
                POS_Y,
                POS_Z,
                Link,
                Hours,
                Minutes,
                Seconds,
                Timestamp,
                InGameTime
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ''', pvp_data)
            # Commit changes and close the connection
            conn.commit()
        else:
            PVP_Limit = True;
            print(f"Record with Timestamp {timestamp} already exists in the PVP table.")        

    elif (Killer != "" and Victim != "" and Weapon != "" and WeaponType != "" and Distance != "" and HitLocation != "" and HitDamage != "" and POS_X != "" and POS_Y != "" and POS_Z != "" and Link != ""):
        print(f"Killer: {Killer}, Victim: {Victim}, coords: {POS_X}/{POS_Y}")
        
        #print(f"Killer: {Killer}, Victim: {Victim}, coords: {POS_X}/{POS_Y} Time Alive: Hours: {hours}, Minutes: {minutes}, Seconds: {seconds} | TimeStamp: {timestamp} | InGameTime: {InGameTime}")
        pvp_data = (
            Killer,
            Victim,
            Weapon,
            WeaponType,
            Distance,
            HitLocation,
            HitDamage,
            POS_X,
            POS_Y,
            POS_Z,
            Link,
            hours,
            minutes,
            seconds,
            timestamp,
            InGameTime
        )
        if not is_Record_in_db(cursor, Victim, timestamp, InGameTime, Link, "PVP"):
            cursor.execute('''
            INSERT INTO PVP (
                Killer,
                Victim,
                Weapon,
                WeaponType,
                Distance,
                HitLocation,
                HitDamage,
                POS_X,
                POS_Y,
                POS_Z,
                Link,
                Hours,
                Minutes,
                Seconds,
                Timestamp,
                InGameTime
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ''', pvp_data)
            # Commit changes and close the connection
            conn.commit()
        else:
            PVP_Limit = True;
            print(f"Record with Timestamp {timestamp} already exists in the PVP table.")        

def processPVEDesc(description,title,timestamp):
    global PVE_Limit
    latitude_longitude= ''
    Victim = ''
    data = []
    LineCounter = 0
    #lines = description.split('\n')
    
    Victim = ''
    POS_X = ''
    POS_Y = ''
    POS_Z = ''
    hours = ''
    minutes = ''
    seconds = ''
    
    InGameTime = title.replace("PVE Event -", "").strip()
 
    
    DescArray = description.splitlines()

    if(len(DescArray)== 3):      #location? 
         
        Name_Text = DescArray[0]	
        Name_Pattern = r"\*\*([A-Za-z0-9\_\-]+)\*\*" #name
        Name_Match = re.search(Name_Pattern , Name_Text)
        
        Location_Text = DescArray[1]	
        Location_Pattern = r'\*\*Location\*\* \[(.*?);(.*?);(.*?)\]\((.*?)\)'
        Location_Match = re.search(Location_Pattern , Location_Text)
     
        Time_Alive_Text = DescArray[2]
        Time_Alive_pattern = r"\*\*Time Alive\*\*\s(\d{2})\*\*H\*\* (\d{2})\*\*M\*\* (\d{2})\*\*S\*\*"
        Time_Alive_match = re.search(Time_Alive_pattern, Time_Alive_Text)
            


        if Name_Match:
            Victim = Name_Match.group(1)
        if Location_Match:
            POS_X = Location_Match.group(1)
            POS_Y = Location_Match.group(2)
            POS_Z = Location_Match.group(3)
            Link = Location_Match.group(4)
        
            if Time_Alive_match:
                hours = Time_Alive_match.group(1)
                minutes = Time_Alive_match.group(2)
                seconds = Time_Alive_match.group(3)
               
            print(f"Victim: {Victim}, coords: {POS_X}/{POS_Y}")

            #print(f"Victim: {Victim}, coords: {POS_X}/{POS_Y} Time Alive: Hours: {hours}, Minutes: {minutes}, Seconds: {seconds} | TimeStamp: {timestamp} | InGameTime: {InGameTime}")
            pve_data = (
                Victim,
                POS_X,
                POS_Y,
                POS_Z,
                Link,
                hours,
                minutes,
                seconds,
                timestamp,
                InGameTime
            )
            if not is_Record_in_db(cursor, Victim, timestamp, InGameTime, Link, "PVE"):
                cursor.execute('''
                    INSERT INTO PVE (
                        Victim,
                        POS_X,
                        POS_Y,
                        POS_Z,
                        Link,
                        Hours,
                        Minutes,
                        Seconds,
                        Timestamp,
                        InGameTime
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ''', pve_data)
                # Commit changes and close the connection
                conn.commit()
            else:
                PVE_Limit = True;
                print(f"Record with Timestamp {timestamp} already exists in the PVE table.")
            

           

def processPVP(response):
    global PVP_lastID
    global PVP_Limit
    
    parsed_json = json.loads(response.content)
    title = ''
    timestamp = ''
    fields = ''
    for json_obj in parsed_json:
        currentID = json_obj['id']
        
        if PVP_lastID == currentID:
            pass
        else:
            PVP_lastID = currentID
            if "embeds" in json_obj:
                for embed in json_obj["embeds"]:
                    if(PVP_Limit == False):
                        if "title" in embed:
                            title = embed["title"]
                        if "timestamp" in embed:
                            timestamp = embed["timestamp"]
                            
                        if "fields" in embed:
                            fields = embed["fields"]
                        if "description" in embed:
                            description = embed["description"]
                            processPVPDesc(description,title,timestamp,fields)
                    else:
                        break
                
 
def processPVE(response):
    global PVE_lastID
    global PVE_Limit
    
    parsed_json = json.loads(response.content)
    currentID = ''
    for json_obj in parsed_json:
        currentID = json_obj['id']
        if "embeds" in json_obj:
            for embed in json_obj["embeds"]:
                if(PVE_Limit == False):
                    if "title" in embed:
                        title = embed["title"]
                    if "timestamp" in embed:
                        timestamp = embed["timestamp"]
                    if "description" in embed:
                        description = embed["description"]
                        processPVEDesc(description,title,timestamp)
                else:
                    break
    return currentID


def PVP():
    global maxPages
    global PVP_lastID
    global PVP_params
    global PVP_cookies
    global PVP_headers
    global previous_PVP_lastID
    global PVP_Limit

    print("Generating PVP")

    for i in range(0, maxPages):
        if(PVP_Limit == False):
            system("title " + f"page: {i+1} / {maxPages}")
            print("title " + f"page: {i+1} / {maxPages}")
            if PVP_lastID == '':
               PVP_params = {'limit': '50'}
            else:
                PVP_params = {'before': PVP_lastID, 'limit': '50'}
            #print(PVP_params)
            response = requests.get(
            'https://discord.com/api/v9/channels/' + PVP_CHANNAL_ID + '/messages',
                params=PVP_params,
                cookies=PVP_cookies,
                headers=PVP_headers,
            )
            if response.status_code == 403:
                print("PVP Channel is unavalible.")
                break
            else:
                if response.status_code == 200:
                    # Store the current value of PVP_lastID before processing
                    previous_PVP_lastID = PVP_lastID
                    processPVP(response)
                    # Check if PVP_lastID remains the same after processing
                    if PVP_lastID == previous_PVP_lastID:
                        print("No change in PVP_lastID. Exiting loop.")
                        break  # Exit the loop if PVP_lastID doesn't change
                else:
                    print(f"Received a {response.status_code} response. Handle it accordingly.")
                time.sleep(2)
        else:
            break


def PVE():
    global maxPages
    global PVE_lastID
    global PVE_params
    global PVE_cookies
    global PVE_headers
    global previous_PVE_lastID
    global PVE_Limit

    print("Generating PVE") 


    for i in range(0, maxPages):
        if(PVE_Limit == False):
            system("title " + f"page: {i+1} / {maxPages}")
            print("title " + f"page: {i+1} / {maxPages}")
            if PVE_lastID == '':
               PVE_params = {'limit': '50'}
            else:
                PVE_params = {'before': PVE_lastID, 'limit': '50'}
            print(PVE_params)
            response = requests.get(
                'https://discord.com/api/v9/channels/' + PVE_CHANNAL_ID + '/messages',
                params=PVE_params,
                cookies=PVE_cookies,
                headers=PVE_headers,
            )
            if response.status_code == 403:
                print("PVE Channel is unavalible.")
                break
            else:
                if response.status_code == 200:
                    CurrentID = processPVE(response)
                    if PVE_lastID == CurrentID:
                        print("No change in PVE_lastID. Exiting loop.")
                        break
                    else:
                        PVE_lastID = CurrentID
                else:
                    print(f"Received a {response.status_code} response. Handle it accordingly.")
                time.sleep(2)
        else:
            break

if __name__ == "__main__":
    # Create a connection to the database (or create a new one if it doesn't exist)
    conn = sqlite3.connect('PVP_PVE.db')
    # Create a cursor object to execute SQL commands
    cursor = conn.cursor()
    
    # Execute SQL commands
    cursor.execute('''
    CREATE TABLE IF NOT EXISTS PVP (
        id INTEGER PRIMARY KEY,
        Killer TEXT,
        Victim TEXT,
        Weapon TEXT,
        WeaponType TEXT,
        Distance REAL,
        HitLocation TEXT,
        HitDamage INTEGER,
        POS_X REAL,
        POS_Y REAL,
        POS_Z REAL,
        Link TEXT,
        Hours INTEGER,
        Minutes INTEGER,
        Seconds INTEGER,
        Timestamp TEXT,
        InGameTime TEXT
    )
    ''')
    cursor.execute('''
    CREATE TABLE IF NOT EXISTS PVE (
        id INTEGER PRIMARY KEY,
        Victim TEXT,
        POS_X REAL,
        POS_Y REAL,
        POS_Z REAL,
        Link TEXT,
        Hours INTEGER,
        Minutes INTEGER,
        Seconds INTEGER,
        Timestamp TEXT,
        InGameTime TEXT
    )
    ''')
    
    PVP()
    PVE()
    
    conn.close()
    
    CopyDB()

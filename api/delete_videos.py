#/usr/bin/python
import os, shutil, sys
event_path = os.path.abspath("event.txt")
asset_path = os.path.abspath("asset.txt")
'''if os.path.exists(asset_path):
    with open(asset_path, "r") as f:
        for line in f:
            asset_video_cache_path ="/volumes/data/assets/cache/"+str(line)
            asset_video_trash_path ="/volumes/data/assets/trash/"+str(line)
            print asset_video_cache_path, asset_video_trash_path
            try:
                os.system("rm -rf /volumes/data/assets/cache/"+ str(line))
                os.system("rm -rf /volumes/data/assets/trash/"+ str(line))
            except Exception, e:
                print str(e)'''


if os.path.exists(event_path):
    with open(event_path, "r") as f:
        for line in f:
            event_video_cache_path ="/volumes/data/events/cache/"+str(line)
            event_video_trash_path ="/volumes/data/events/trash/"+str(line)
            print event_video_cache_path, event_video_trash_path
            try:
                os.system("rm -rf "+ str(event_video_cache_path))
                os.system("rm -rf "+ str(event_video_trash_path))
            except Exception, e:
                print str(e)

sys.exit(0)

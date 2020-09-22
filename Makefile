clean:
	@echo "Stopping Containers..." ;\
    docker stop $(docker ps -a -q --filter="name=mysql-") ;\
    @echo "Removing Containers..." ;\
    docker rm $(docker ps -a -q --filter="name=mysql-") ;\
    @echo "Cleaning Directory Master" ;\
    rm -rf master/backup/* ;\
    rm -rf master/data/* ;\
    rm -rf master/log/* ;\
    @echo "Cleaning Directory Slave" ;\
    rm -rf slave/backup/* ;\
    rm -rf slave/data/* ;\
    rm -rf slave/log/* ;\
    @echo "Done"

fix-rights:
	sudo chmod 777 -R slave master ;\
    sudo chmod 0444 master/conf.d/master.cnf  ;\
    sudo chmod 0444 slave/conf.d/slave.cnf

up:
	docker-compose up -d

master:
	docker exec -i -t mysql-master /bin/bash

slave:
	docker exec -i -t mysql-master /bin/bash

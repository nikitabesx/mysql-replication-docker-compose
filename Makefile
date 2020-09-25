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
	sudo chmod 777 -R slave master slave-second;\
    sudo chmod 0444 master/conf.d/master.cnf  ;\
    sudo chmod 0444 slave/conf.d/slave.cnf ;\
    sudo chmod 0444 slave-second/conf.d/slave.cnf

fix-config-rights:
	sudo chmod 0444 slave/conf.d/slave.cnf ;\
    sudo chmod 0444 slave-second/conf.d/slave.cnf ;\
    sudo chmod 0444 master/conf.d/master.cnf

up:
	docker-compose up -d

m-master:
	docker exec -i -t mysql-master /bin/bash
mm: m-master

m-slave:
	docker exec -i -t mysql-slave /bin/bash
ms: m-slave

m-slave-second:
	docker exec -i -t mysql-slave /bin/bash
mss: m-slave-second

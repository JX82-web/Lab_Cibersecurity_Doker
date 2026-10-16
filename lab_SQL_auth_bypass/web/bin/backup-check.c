#include <unistd.h>
#include <stdlib.h>

int main(void) {
    setuid(0);
    setgid(0);
    system("tar -czf /tmp/backup.tgz /var/www/html");
    return 0;
}

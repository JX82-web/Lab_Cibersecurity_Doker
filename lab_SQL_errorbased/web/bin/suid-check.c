#include <unistd.h>
#include <stdlib.h>
#include <stdio.h>

int main(int argc, char *argv[]) {
    // Verifica che siano stati passati almeno il file di origine e la destinazione
    if (argc < 3) {
        fprintf(stderr, "Uso: %s <origine> <destinazione> [altri_argomenti...]\n", argv[0]);
        return 1;
    }

    // Specifichiamo il percorso assoluto del binario di sistema 'cp'
    char *binary = "/bin/cp";

    /* 
       Creiamo un nuovo array per gli argomenti da passare a 'cp'.
       La dimensione sarà 'argc' perché:
       - Sostituiamo il primo argomento (argv[0], il nome di questo programma) con '/bin/cp'
       - Manteniamo tutti gli altri argomenti (file1, file2, destinazione, flag)
       - Lo spazio è già sufficiente e include il NULL finale ereditato da argv
    */
    char **new_argv = malloc((argc + 1) * sizeof(char *));
    if (new_argv == NULL) {
        perror("Errore nell'allocazione della memoria");
        return 1;
    }

    // Il primo argomento per convenzione è il percorso del programma da eseguire
    new_argv[0] = binary;

    // Copiamo tutti gli argomenti successivi passati dall'utente
    for (int i = 1; i < argc; i++) {
        new_argv[i] = argv[i];
    }

    // L'array deve terminare obbligatoriamente con NULL
    new_argv[argc] = NULL;

    // Definiamo un ambiente vuoto (sicuro) per l'esecuzione
    char *envp[] = { NULL };

    // Eseguiamo il comando cp sostituendo il processo corrente
    execve(binary, new_argv, envp);

    // Se execve fallisce, restituisce un errore (altrimenti il codice sotto non viene mai raggiunto)
    perror("Errore durante l'esecuzione di execve");
    free(new_argv);
    return 1;
}


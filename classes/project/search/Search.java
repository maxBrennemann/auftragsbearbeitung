import java.io.BufferedReader; 
import java.io.IOException; 
import java.io.InputStreamReader; 

public class Search {

    public static DocumentCollection documentCollectionCustomers;
    public static DocumentCollection documentCollectionOrders;
    public static DocumentCollection documentCollectionProducts;

    /*
    * syntax:
    * add [type] [id] [data] -> returns currently nothing
    * search [type] [query] -> returns id list of potential matches
    */

    public static void main(String[] args) throws IOException {
        String command = "";
        BufferedReader reader = new BufferedReader(new InputStreamReader(System.in));

        mainloop: while (true) {
            command = reader.readLine();
            System.out.println(command);
            if (command.equals("stopProgram")) {
                break mainloop;
            }
        }

        if (args[0].equals("add")) {
            DocumentType type = DocumentType.valueOf(args[1]);
            String data = args[2];

            switch (type) {
                case Customer:
                    break;
                case Order:
                    break;
                case Product:
                    break;
                default:
                    break;
            }
        }
    }
    
}

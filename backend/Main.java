package backend;
import com.sun.net.httpserver.HttpServer;
import java.net.InetSocketAddress;

public class Main {
    public static void main(String[] args) throws Exception {
        HttpServer server = HttpServer.create(new InetSocketAddress(8080), 0);        
        server.createContext("/api/order", new OrderHandler());
        server.setExecutor(null);
        server.start();
        System.out.println("Backend Java berjalan pada port 8080...");
    }
}
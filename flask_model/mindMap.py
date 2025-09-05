import matplotlib.pyplot as plt
import networkx as nx

# Define the structure of the mind map
mind_map = {
    "IntelliFlight": [
        "User Panel",
        "Booking System",
        "Services",
        "Payment & Billing",
        "Reviews & Ratings",
        "Admin Panel"
    ],
    "User Panel": [
        "Login / Registration",
        "Dashboard",
        "Profile Management"
    ],
    "Booking System": [
        "Flight Search",
        "Fare Prediction",
        "Flight Details / Selection",
        "Seats Booking",
        "Booking History"
    ],
    "Fare Prediction": [
        "Predicted Fare",
        "Recommended / Nearest Price / Cheapest Flight"
    ],
    "Services": [
        "Cab Booking",
        "Hotel Booking",
        "Food Booking",
        "Combo Packages"
    ],
    "Cab Booking": [
        "Pickup / Drop Location",
        "Car Selection"
    ],
    "Hotel Booking": [
        "Hotel Details",
        "Room & Guest Selection"
    ],
    "Food Booking": [
        "Menu Selection",
        "Add to Booking"
    ],
    "Combo Packages": [
        "Average",
        "Best",
        "Premium"
    ],
    "Payment & Billing": [
        "Payment Gateway",
        "Final Bill Summary",
        "Promo / Discount Codes",
        "Payment Status"
    ],
    "Reviews & Ratings": [
        "Submit Review",
        "Rating Stars",
        "Display Reviews"
    ],
    "Admin Panel": [
        "User Management",
        "Booking Management",
        "Food Management",
        "Hotel Management",
        "Cab Management",
        "Insurance Management",
        "Reports & Analytics"
    ]
}

# Build directed graph
G = nx.DiGraph()
for parent, children in mind_map.items():
    for child in children:
        G.add_edge(parent, child)

# Custom position: put IntelliFlight at the center
pos = nx.spring_layout(G, k=1.2, iterations=200, seed=42)
pos["IntelliFlight"] = [0, 0]  # force center

# Categorize nodes
main_node = ["IntelliFlight"]
main_headings = mind_map["IntelliFlight"]
sub_nodes = [n for n in G.nodes() if n not in main_node + main_headings]

# Edge groups
admin_edges = [(u, v) for u, v in G.edges() if u == "Admin Panel"]
reviews_edges = [(u, v) for u, v in G.edges() if u == "Reviews & Ratings"]
other_edges = [(u, v) for u, v in G.edges() if (u != "Admin Panel" and u != "Reviews & Ratings")]

# Draw edges with different colors
nx.draw_networkx_edges(G, pos, edgelist=admin_edges, edge_color="black", width=1.8)
nx.draw_networkx_edges(G, pos, edgelist=reviews_edges, edge_color="red", width=1.8)
nx.draw_networkx_edges(G, pos, edgelist=other_edges, edge_color="gray", alpha=0.6, width=1.2)

# Draw nodes with colors
nx.draw_networkx_nodes(G, pos, nodelist=main_node, node_size=4200, node_color="gold", edgecolors="black", linewidths=2)
nx.draw_networkx_nodes(G, pos, nodelist=main_headings, node_size=3500, node_color="skyblue", edgecolors="black", linewidths=1.5)
nx.draw_networkx_nodes(G, pos, nodelist=sub_nodes, node_size=2800, node_color="lightgreen", edgecolors="black")

# Labels
labels = {node: node for node in G.nodes()}
nx.draw_networkx_labels(G, pos, labels, font_size=9, font_weight="bold")

# Title
plt.title("Mind Map – IntelliFlight: AI-Driven Flight Booking & Fare Estimation",
          fontsize=14, fontweight="bold", pad=20)

plt.axis("off")
plt.show()

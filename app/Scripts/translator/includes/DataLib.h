#ifndef DATALIB_H
#define DATALIB_H

class Data {
private:
    double distance;
    double RA;
    double DEC;

public:
    Data(double distance, double RA, double DEC) : distance(distance), RA(RA), DEC(DEC) {}

    Data SphericalTransData(const Data& ref_data) const {
        double ref_distance = ref_data.getDistance();
        double ref_DEC = ref_data.getDEC() * (M_PI / 180.0);
        double ref_RA = ref_data.getRA() * (M_PI / 180.0);

        double RAp = RA * (M_PI / 180.0);
        double DECp = DEC * (M_PI / 180.0);
        double DISTp = distance;

        double theta = M_PI / 2 - DECp;

        double xs = ref_distance * cos(ref_DEC) * cos(ref_RA) - DISTp * cos(DECp) * cos(RAp);
        double ys = ref_distance * cos(ref_DEC) * sin(ref_RA) - DISTp * cos(DECp) * sin(RAp);
        double zs = ref_distance * sin(ref_DEC) - DISTp * sin(DECp);

        double xs2 = cos(theta) * cos(RAp) * xs + cos(theta) * sin(RAp) * ys - sin(theta) * zs;
        double ys2 = -sin(RAp) * xs + cos(RAp) * ys;
        double zs2 = sin(theta) * cos(RAp) * xs + sin(theta) * sin(RAp) * ys + cos(theta) * zs;

        std::cout << ref_distance <<" , " << ref_DEC << " , " << ref_RA << " +++ ";
        std::cout << DISTp <<" , " << DECp << " , " << RAp << " +++ ";
        std::cout << xs <<" , " << ys << " , " << zs << " +++ ";
        std::cout << xs2 <<" , " << ys2 << " , " << zs2 << " +++ ";
        double final_dist= sqrt(pow(xs2, 2) + pow(ys2, 2) + pow(zs2, 2));
        double final_ra = asin(zs2 / sqrt(pow(xs2, 2) + pow(ys2, 2) + pow(zs2, 2)));
        double final_dec = atan2(ys2, xs2);
        std::cout << "Dist=" << final_dist << ", RA=" << final_ra << ", DEC=" << final_dec << std::endl;
        return Data(final_dist, final_ra, final_dec);
    }

    double ExoPlanetToStarDistance(double DistExo) const {
        return sqrt(abs(pow(distance, 2) - pow(DistExo, 2)));
    }

    double getDistance() const { return distance; }
    double getRA() const { return RA; }
    double getDEC() const { return DEC; }

    void setDistance(double distance) { this->distance = distance; }
    void setRA(double RA) { this->RA = RA; }
    void setDEC(double DEC) { this->DEC = DEC; }
};

#endif
